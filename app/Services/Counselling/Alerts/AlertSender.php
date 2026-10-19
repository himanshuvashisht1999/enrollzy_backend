<?php

namespace App\Services\Counselling\Alerts;

use App\Mail\Counselling\AlertMail;
use App\Models\Counselling\AlertDelivery;
use App\Models\Counselling\Subscription;
use App\Services\Counselling\WhatsApp\WhatsApp;
use Illuminate\Support\Facades\Mail;

/**
 * Delivers queued alerts, one channel at a time. Consent is checked AGAIN at send time, so someone who unsubscribed
 * after the alert was queued is skipped. A channel that cannot deliver is marked skipped/failed, never "sent".
 */
class AlertSender
{
    /** @return array{sent: int, failed: int, skipped: int} */
    public function send(?int $limit = null): array
    {
        $limit ??= config('cns.alerts.max_deliveries_per_run');
        $counts = ['sent' => 0, 'failed' => 0, 'skipped' => 0];
        $provider = WhatsApp::provider();

        foreach (AlertDelivery::with(['alert', 'subscriber'])->where('status', 'pending')->orderBy('id')->limit($limit)->get() as $d) {
            [$status, $error] = $this->deliver($d, $provider);
            $d->forceFill(['status' => $status, 'error' => $error ? mb_substr($error, 0, 500) : null, 'attempts' => $d->attempts + 1,
                'sent_at' => $status === 'sent' ? now() : null])->save();
            $counts[$status]++;
        }
        return $counts;
    }

    /** Failed deliveries can be retried a few times (admin action). */
    public function retryFailed(): int
    {
        return AlertDelivery::where('status', 'failed')->where('attempts', '<', 3)->update(['status' => 'pending']);
    }

    /** @return array{0: string, 1: ?string} */
    private function deliver(AlertDelivery $d, $provider): array
    {
        $alert = $d->alert;
        $s = $d->subscriber;
        $sub = Subscription::where('subscriber_id', $s->id)->where('target_type', $alert->target_type)->where('target_id', $alert->target_id)->first();

        // Still wanted, still confirmed?
        $flag = ['email' => 'via_email', 'whatsapp' => 'via_whatsapp', 'inapp' => 'via_inapp'][$d->channel];
        $identity = ['email' => $s->canEmail(), 'whatsapp' => $s->canWhatsApp(), 'inapp' => $s->canInApp()][$d->channel];
        if (!$sub || !$sub->confirmed_at || !$sub->$flag || !$identity) {
            return ['skipped', 'No longer subscribed on this channel.'];
        }

        try {
            switch ($d->channel) {
                case 'inapp':
                    return ['sent', null];     // the inbox reads sent in-app deliveries
                case 'email':
                    Mail::to($s->email)->send(new AlertMail($alert, FollowLinks::unsubscribe($s, $sub, 'email'), FollowLinks::manage($s)));
                    return ['sent', null];
                case 'whatsapp':
                    if (!$provider->configured()) {
                        return ['skipped', 'WhatsApp provider is not configured.'];
                    }
                    $r = $provider->sendAlert($s->phone, $alert->title, $alert->link_url ?: config('cns.frontend_url'));
                    return $r['ok'] ? ['sent', null] : ['failed', $r['error'] ?? 'WhatsApp send failed.'];
            }
        } catch (\Throwable $e) {
            return ['failed', $e->getMessage()];
        }
        return ['skipped', 'Unknown channel.'];
    }
}
