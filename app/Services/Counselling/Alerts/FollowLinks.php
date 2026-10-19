<?php

namespace App\Services\Counselling\Alerts;

use App\Models\Counselling\Subscriber;
use App\Models\Counselling\Subscription;
use Illuminate\Support\Facades\URL;

/** Signed links that go into emails: confirm an address, and unsubscribe (one channel or everything). */
class FollowLinks
{
    public static function confirm(Subscriber $s, ?\App\Models\Counselling\Subscription $sub = null): string
    {
        // Bound to the current address: changing the email invalidates old links.
        return URL::temporarySignedRoute('api.v1.follow.confirm', now()->addDays(3), array_filter(['s' => $s->id, 'sub' => $sub?->id, 'e' => substr(hash('sha256', (string) $s->email), 0, 16)]));
    }

    /** channel: email | whatsapp | inapp | all. With no subscription it applies to everything the subscriber follows. */
    public static function unsubscribe(Subscriber $s, ?Subscription $sub = null, string $channel = 'all'): string
    {
        return URL::signedRoute('api.v1.unsubscribe', array_filter(['s' => $s->id, 'sub' => $sub?->id, 'ch' => $channel]));
    }

    public static function manage(Subscriber $s): string
    {
        return config('cns.frontend_url') . '/alerts?token=' . urlencode($s->token());
    }
}
