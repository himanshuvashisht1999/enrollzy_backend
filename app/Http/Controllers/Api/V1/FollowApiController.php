<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\Counselling\ConfirmFollowMail;
use App\Models\Counselling\AlertDelivery;
use App\Models\Counselling\Subscriber;
use App\Models\Counselling\Subscription;
use App\Services\Counselling\Alerts\FollowLinks;
use App\Services\Counselling\PublicPresenter;
use App\Services\Counselling\WhatsApp\WhatsApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * Follow a counselling or exam and get alerts. No student accounts: a subscriber is an email and/or phone that has
 * been CONFIRMED (email link, phone code). Nothing is sent to an unconfirmed address, WhatsApp needs an explicit opt-in,
 * and every channel can be switched off on its own.
 */
class FollowApiController extends Controller
{
    public function __construct(private PublicPresenter $presenter)
    {
    }

    /** POST /api/v1/follow */
    public function follow(Request $request): JsonResponse
    {
        // Honeypot: real forms leave this empty. Answer like a success so bots learn nothing.
        if ($request->filled('website')) {
            return response()->json(['message' => 'Check your email or phone to confirm.'], 202);
        }

        $data = $request->validate([
            'type' => ['required', Rule::in(['counselling', 'exam'])],
            'slug' => 'required|string|max:255',
            'email' => 'nullable|email:rfc|max:190',
            'phone' => 'nullable|string|max:25',
            'channels' => 'required|array|min:1',
            'channels.*' => [Rule::in(['email', 'whatsapp', 'inapp'])],
            'whatsapp_opt_in' => 'nullable|boolean',
            'alert_types' => 'nullable|array',
            'alert_types.*' => [Rule::in(Subscription::KINDS)],
            'token' => 'nullable|string|max:200',
        ]);

        // A valid inbox token proves this is the same person who already confirmed: use that subscriber as is.
        $known = !empty($data['token']) ? Subscriber::fromToken($data['token']) : null;
        if ($known) {
            $data['email'] = $known->email_confirmed_at ? $known->email : null;
            $data['phone'] = $known->phone_confirmed_at ? $known->phone : null;
        }

        $email = Subscriber::normalizeEmail($data['email'] ?? null);
        $phone = Subscriber::normalizePhone($data['phone'] ?? null);
        if (!empty($data['phone']) && !$phone) {
            return response()->json(['message' => 'Enter a valid phone number, with the country code if it is not an Indian number.', 'errors' => ['phone' => ['Invalid phone number.']]], 422);
        }
        $channels = array_unique($data['channels']);
        $errors = [];
        if (in_array('email', $channels) && !$email) $errors['email'] = ['An email is needed for email alerts.'];
        if (in_array('whatsapp', $channels)) {
            if (!$phone) $errors['phone'] = ['A phone number is needed for WhatsApp alerts.'];
            if (!$request->boolean('whatsapp_opt_in')) $errors['whatsapp_opt_in'] = ['Tick the box to agree to receive WhatsApp messages.'];
        }
        if (!$email && !$phone) $errors['email'] = ['Give an email or a phone number.'];
        if ($errors) {
            return response()->json(['message' => 'Please check the form.', 'errors' => $errors], 422);
        }

        [$type, $targetId, $targetName] = $this->target($data['type'], $data['slug']);

        $subscriber = DB::transaction(function () use ($email, $phone) {
            $s = ($email ? Subscriber::where('email', $email)->first() : null) ?? ($phone ? Subscriber::where('phone', $phone)->first() : null) ?? new Subscriber();
            if (!$s->email && $email && !Subscriber::where('email', $email)->exists()) $s->email = $email;
            // A number already confirmed by someone else is never attached to this subscriber.
            if (!$s->phone && $phone && !Subscriber::where('phone', $phone)->where('id', '!=', $s->id ?? 0)->exists()) $s->phone = $phone;
            $s->save();
            return $s;
        });

        $sub = Subscription::firstOrNew(['subscriber_id' => $subscriber->id, 'target_type' => $type, 'target_id' => $targetId]);
        foreach (['email', 'whatsapp', 'inapp'] as $ch) {
            if (in_array($ch, $channels)) $sub->{'via_' . $ch} = true;
        }
        if (array_key_exists('alert_types', $data)) $sub->alert_types = $data['alert_types'] ?: null;
        if ($known && !$sub->confirmed_at) {
            $sub->confirmed_at = now();
        }
        $sub->save();

        // Anyone can type any email, so every NEW follow is confirmed on its own, even for an address that is already confirmed.
        $needsEmail = !$sub->confirmed_at && $email && $subscriber->email === $email && ($sub->via_email || $sub->via_inapp);
        $needsPhone = !$sub->confirmed_at && $phone && $subscriber->phone === $phone && $sub->via_whatsapp;
        if ($needsEmail) {
            Mail::to($subscriber->email)->send(new ConfirmFollowMail(FollowLinks::confirm($subscriber, $sub), $targetName, FollowLinks::unsubscribe($subscriber, $sub)));
        }
        if ($needsPhone) {
            $this->sendCode($subscriber);
        }

        // Same answer whether or not this address was already known.
        return response()->json([
            'message' => ($needsEmail || $needsPhone) ? 'Almost done: confirm to start getting alerts.' : ($sub->confirmed_at ? 'You are following ' . $targetName . '.' : 'Almost done: confirm to start getting alerts.'),
            'pending_confirmation' => ['email' => (bool) $needsEmail, 'phone' => (bool) $needsPhone],
        ], 202);
    }

    /** POST /api/v1/follow/verify-phone {phone, code} */
    public function verifyPhone(Request $request): JsonResponse
    {
        $data = $request->validate(['phone' => 'required|string|max:25', 'code' => 'required|digits:6']);
        $s = Subscriber::where('phone', Subscriber::normalizePhone($data['phone']))->first();
        $fail = fn () => response()->json(['message' => 'That code is not valid or has expired.'], 422);

        if (!$s || !$s->phone_code_hash || !$s->phone_code_expires_at || $s->phone_code_expires_at->isPast() || $s->phone_code_attempts >= 5) {
            return $fail();
        }
        $s->increment('phone_code_attempts');
        if (!hash_equals($s->phone_code_hash, $this->codeHash($s, $data['code']))) {
            return $fail();
        }
        // The code proves the number is theirs; asking for WhatsApp alerts with the opt-in box ticked is the consent.
        $s->forceFill([
            'phone_confirmed_at' => $s->phone_confirmed_at ?? now(),
            'whatsapp_opt_in_at' => $s->subscriptions()->where('via_whatsapp', true)->exists() ? ($s->whatsapp_opt_in_at ?? now()) : $s->whatsapp_opt_in_at,
            'phone_code_hash' => null, 'phone_code_expires_at' => null, 'phone_code_attempts' => 0,
        ])->save();

        $s->subscriptions()->whereNull('confirmed_at')->where('via_whatsapp', true)->update(['confirmed_at' => now()]);

        return response()->json(['confirmed' => true, 'inbox_token' => $s->token()]);
    }

    /** GET /api/v1/follow/confirm?... (signed link from the email): confirms the address and shows a small page. */
    public function confirmEmail(Request $request)
    {
        $s = Subscriber::find((int) $request->query('s'));
        if (!$s || !$s->email || substr(hash('sha256', $s->email), 0, 16) !== $request->query('e')) {
            return $this->notice($request, 'This link is no longer valid', 'Ask for a new confirmation from the follow form.', 410);
        }
        if (!$s->email_confirmed_at) {
            $s->forceFill(['email_confirmed_at' => now()])->save();
        }
        $s->subscriptions()->whereNull('confirmed_at')->when($request->query('sub'), fn ($q, $id) => $q->where('id', $id), fn ($q) => $q->where(fn ($w) => $w->where('via_email', true)->orWhere('via_inapp', true)))
            ->update(['confirmed_at' => now()]);
        $data = ['confirmed' => true, 'inbox_token' => $s->token(), 'manage_url' => FollowLinks::manage($s)];
        return $this->notice($request, 'Email confirmed', 'You will now get alerts about what you follow. Every email has an unsubscribe link.', 200, $data);
    }

    /** GET /api/v1/unsubscribe?... (signed): switch off one channel, or everything, for one follow or all of them. */
    public function unsubscribe(Request $request)
    {
        $s = Subscriber::find((int) $request->query('s'));
        $channel = $request->query('ch', 'all');
        if (!$s || !in_array($channel, ['email', 'whatsapp', 'inapp', 'all'], true)) {
            return $this->notice($request, 'This link is no longer valid', '', 410);
        }
        $subs = $s->subscriptions()->when($request->query('sub'), fn ($q, $id) => $q->where('id', $id))->get();
        foreach ($subs as $sub) {
            foreach (['email', 'whatsapp', 'inapp'] as $ch) {
                if ($channel === 'all' || $channel === $ch) $sub->{'via_' . $ch} = false;
            }
            $sub->save();
        }
        // Anything queued but not yet sent on that channel is dropped.
        AlertDelivery::where('subscriber_id', $s->id)->where('status', 'pending')->when($channel !== 'all', fn ($q) => $q->where('channel', $channel))->update(['status' => 'skipped', 'error' => 'Unsubscribed.']);

        return $this->notice($request, 'You are unsubscribed', $channel === 'all' ? 'You will not get any more alerts for this.' : 'You will not get ' . $channel . ' alerts for this any more.', 200, ['unsubscribed' => true, 'channel' => $channel]);
    }

    // ---- inbox + management: all by token ----

    /** GET /api/v1/inbox?token=...&unread=1 */
    public function inbox(Request $request): JsonResponse
    {
        $s = $this->subscriber($request);
        $q = AlertDelivery::with('alert')->where('subscriber_id', $s->id)->where('channel', 'inapp')->where('status', 'sent');
        $unread = (clone $q)->whereNull('read_at')->count();
        $page = $q->when($request->boolean('unread'), fn ($b) => $b->whereNull('read_at'))->orderByDesc('id')->paginate(max(1, min(50, (int) $request->input('per_page', 20))))->withQueryString();

        return response()->json([
            'unread_count' => $unread,
            'data' => $page->getCollection()->map(fn ($d) => [
                'id' => $d->id, 'kind' => $d->alert->kind, 'title' => $d->alert->title, 'body' => $d->alert->body, 'link_url' => $d->alert->link_url,
                'source_url' => $d->alert->source_url, 'created_at' => $d->alert->created_at->toIso8601String(), 'read' => $d->read_at !== null,
            ])->all(),
            'meta' => ['page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'last_page' => $page->lastPage()],
        ]);
    }

    /** POST /api/v1/inbox/read {token, ids?: []} : no ids = mark everything read */
    public function markRead(Request $request): JsonResponse
    {
        $s = $this->subscriber($request);
        $n = AlertDelivery::where('subscriber_id', $s->id)->where('channel', 'inapp')->whereNull('read_at')
            ->when($request->input('ids'), fn ($q, $ids) => $q->whereIn('id', (array) $ids))->update(['read_at' => now()]);
        return response()->json(['marked_read' => $n]);
    }

    /** GET /api/v1/follows?token=... */
    public function follows(Request $request): JsonResponse
    {
        $s = $this->subscriber($request);
        return response()->json([
            'identity' => ['email' => $s->email, 'email_confirmed' => (bool) $s->email_confirmed_at, 'phone' => $s->phone, 'phone_confirmed' => (bool) $s->phone_confirmed_at, 'whatsapp_opted_in' => (bool) $s->whatsapp_opt_in_at],
            'data' => $s->subscriptions()->get()->map(fn ($sub) => ($t = $sub->target()) ? [
                'id' => $sub->id, 'target' => $t, 'confirmed' => $sub->isConfirmed(), 'channels' => ['email' => $sub->via_email, 'whatsapp' => $sub->via_whatsapp, 'inapp' => $sub->via_inapp],
                'alert_types' => $sub->alert_types,
            ] : null)->filter()->values()->all(),
        ]);
    }

    /** POST /api/v1/follows/{id}/channels {token, email?, whatsapp?, inapp?, alert_types?} */
    public function updateChannels(Request $request, int $subscription): JsonResponse
    {
        $s = $this->subscriber($request);
        $sub = $s->subscriptions()->findOrFail($subscription);
        $request->validate(['alert_types' => 'nullable|array', 'alert_types.*' => [Rule::in(Subscription::KINDS)]]);
        foreach (['email', 'whatsapp', 'inapp'] as $ch) {
            if ($request->has($ch)) $sub->{'via_' . $ch} = $request->boolean($ch);
        }
        if ($request->has('alert_types')) $sub->alert_types = $request->input('alert_types') ?: null;
        $sub->save();
        return response()->json(['channels' => ['email' => $sub->via_email, 'whatsapp' => $sub->via_whatsapp, 'inapp' => $sub->via_inapp], 'alert_types' => $sub->alert_types]);
    }

    /** DELETE /api/v1/follows/{id}?token=... */
    public function unfollow(Request $request, int $subscription): JsonResponse
    {
        $s = $this->subscriber($request);
        $s->subscriptions()->findOrFail($subscription)->delete();
        return response()->json(['unfollowed' => true]);
    }

    // ---- internals ----

    private function subscriber(Request $request): Subscriber
    {
        $s = Subscriber::fromToken($request->input('token', $request->query('token')));
        abort_unless($s, 401, 'This link is not valid. Confirm your email or phone again.');
        return $s;
    }

    /** @return array{0: string, 1: int, 2: string} */
    private function target(string $type, string $slug): array
    {
        if ($type === 'counselling') {
            $c = $this->presenter->find($slug);
            abort_unless($c, 404, 'Counselling not found.');
            return ['counselling', $c->id, $c->name];
        }
        $e = DB::table('exm_exams')->where('slug', $slug)->whereNull('deleted_at')->where('visibility', 'public')->where('status', true)->first();
        abort_unless($e, 404, 'Exam not found.');
        return ['exam', $e->id, $e->name];
    }

    private function sendCode(Subscriber $s): void
    {
        // At most one code a minute per number.
        if ($s->phone_code_sent_at && $s->phone_code_sent_at->gt(now()->subMinute())) {
            return;
        }
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $s->forceFill(['phone_code_hash' => $this->codeHash($s, $code), 'phone_code_expires_at' => now()->addMinutes(10), 'phone_code_sent_at' => now(), 'phone_code_attempts' => 0])->save();
        WhatsApp::provider()->sendCode($s->phone, $code);
    }

    private function codeHash(Subscriber $s, string $code): string
    {
        return hash_hmac('sha256', 'cns-otp|' . $s->id . '|' . $code, config('app.key'));
    }

    /** JSON for API clients, a small page for people who clicked an email link. */
    private function notice(Request $request, string $title, string $message, int $status, array $data = [])
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $title] + $data, $status);
        }
        return response()->view('public.counselling.notice', ['title' => $title, 'message' => $message, 'manageUrl' => $data['manage_url'] ?? null], $status);
    }
}
