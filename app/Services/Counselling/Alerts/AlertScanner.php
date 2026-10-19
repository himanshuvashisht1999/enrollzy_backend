<?php

namespace App\Services\Counselling\Alerts;

use App\Models\Counselling\Alert;
use App\Models\Counselling\AlertDelivery;
use App\Models\Counselling\Counselling;
use App\Models\Counselling\CounsellingCycle;
use App\Models\Counselling\Round;
use App\Models\Counselling\SeatMatrixVersion;
use App\Models\Counselling\Subscription;
use App\Models\ExamModule\Cycle as ExamCycle;
use App\Models\ExamModule\CycleEvent;
use Illuminate\Support\Facades\DB;

/**
 * Decides what is worth telling followers, and creates each alert exactly once.
 *
 * The rules (exam spec section 7, counselling spec section 16):
 *   - Only VERIFIED data fires an alert: an event must have last_verified_at set AFTER its last change, so a typo that is
 *     fixed before anyone verifies it never reaches students. (Editing a date clears the verification.)
 *   - Each alert has a unique subject_key, so scanning every ten minutes never repeats it.
 *   - Old news is ignored: nothing older than cns.alerts.max_age_days is announced (no flood on first run).
 *   - Only public exams / counsellings and public cycles are announced.
 */
class AlertScanner
{
    /** Windows students can act in: "is now open". */
    private const OPENS = ['registration', 'late-fee-window', 'correction-window', 'admit-card', 'provisional-answer-key', 'answer-key-objection',
        'final-answer-key', 'city-intimation-slip', 'fee-deposit-payment', 'choice-filling', 'reporting-joining', 'document-verification',
        'resignation-window', 'upgradation-window'];
    /** "is out" reads better than "is now open" for these. */
    private const OUT = ['admit-card', 'provisional-answer-key', 'final-answer-key', 'city-intimation-slip'];
    /** Deadlines worth a reminder. */
    private const CLOSES = ['registration', 'late-fee-window', 'correction-window', 'answer-key-objection', 'fee-deposit-payment', 'choice-filling',
        'reporting-joining', 'document-verification', 'resignation-window', 'upgradation-window'];

    private int $created = 0;

    public function scan(): int
    {
        $this->created = 0;
        $this->scanEvents();
        $this->scanStatuses();
        $this->scanSeatMatrices();
        return $this->created;
    }

    // ------------------------------------------------------------------ events

    private function scanEvents(): void
    {
        $verified = fn ($q) => $q->whereNotNull('last_verified_at')->whereColumn('last_verified_at', '>=', 'updated_at');
        $liveCycle = fn ($q) => $q->where('is_public', true)->whereNotIn('cycle_status', ['cancelled', 'completed']);

        $counselling = CycleEvent::with(['type', 'round', 'counsellingCycle.counselling'])->whereNotNull('counselling_cycle_id')->where($verified)
            ->whereHas('counsellingCycle', fn ($q) => $liveCycle($q)->whereHas('counselling', fn ($c) => $c->where('visibility', 'public')->where('is_active', true)))->get();
        foreach ($counselling as $e) {
            $cycle = $e->counsellingCycle;
            $this->event($e, 'counselling', $cycle->counselling_id, $cycle->counselling->name . ' ' . $cycle->title, 'counselling/' . $cycle->counselling->slug);
        }

        $exam = CycleEvent::with(['type', 'cycle.exam'])->whereNotNull('cycle_id')->where($verified)
            ->whereHas('cycle', fn ($q) => $liveCycle($q)->whereHas('exam', fn ($x) => $x->where('visibility', 'public')->where('status', true)))->get();
        foreach ($exam as $e) {
            $this->event($e, 'exam', $e->cycle->exam_id, $e->cycle->exam->name . ' ' . $e->cycle->title, 'exams/' . $e->cycle->exam->slug);
        }
    }

    private function event(CycleEvent $e, string $targetType, int $targetId, string $heading, string $path): void
    {
        $slug = $e->type->slug;
        $manual = $e->type->isManual();
        $what = $e->type->name . ($e->round ? ' (' . $e->round->name . ')' : '') . ($e->label ? ' · ' . $e->label : '');
        $link = config('cns.frontend_url') . '/' . $path;
        $source = $e->source_url ? \App\Models\ExamModule\CycleEvent::safeUrl($e->source_url) : null;
        $cutoff = now()->subDays(config('cns.alerts.max_age_days'));
        $now = now();
        $ts = fn ($d) => $d ? $d->timestamp : 0;
        $end = $e->extended_end_at ?? $e->end_at;

        // --- dates changed / announced (compared with what the scanner saw last time)
        $state = ['s' => $ts($e->start_at), 'e' => $ts($e->end_at), 'x' => $ts($e->extended_end_at), 'p' => $e->date_precision];
        $prev = DB::table('cns_snapshots')->where('subject_type', 'event')->where('subject_id', $e->id)->value('state');
        $prev = $prev ? json_decode($prev, true) : null;
        if ($prev && ($prev['s'] !== $state['s'] || $prev['e'] !== $state['e'] || $prev['x'] !== $state['x']) && $e->updated_at->gte($cutoff)) {
            $announced = !$prev['s'] && $state['s'];
            $this->raise('date_changed', $targetType, $targetId, 'ev:' . $e->id . ':dates:' . md5(json_encode($state)),
                "$heading: $what " . ($announced ? 'dates announced' : 'dates changed'),
                "$what is now: " . $e->dateText() . ($e->note ? "\n" . $e->note : '') . "\nCheck the official notice before you act.", $link, $source);
        }
        $this->snapshot('event', $e->id, $state);

        // --- results and other manually announced items
        if ($manual) {
            if ($e->manual_status === 'declared' && $e->updated_at->gte($cutoff)) {
                $this->raise('declared', $targetType, $targetId, 'ev:' . $e->id . ':declared', "$heading: $what declared",
                    "$what has been declared." . ($e->link_url ? "\nOfficial link: " . $e->link_url : ''), $link, $source ?? \App\Models\ExamModule\CycleEvent::safeUrl($e->link_url));
            }
            return;
        }

        // --- a window that is open right now
        $exact = $e->date_precision === 'exact' && $e->start_at;
        $open = $exact && $e->start_at->lte($now) && (!$end || $now->lte($end));
        if ($open && in_array($slug, self::OPENS) && $e->start_at->gte($cutoff)) {
            $out = in_array($slug, self::OUT);
            $this->raise('opens', $targetType, $targetId, 'ev:' . $e->id . ':open:' . $e->start_at->timestamp,
                "$heading: $what " . ($out ? 'is out' : 'is now open'),
                ($out ? '' : "Open from " . $e->start_at->format('d M Y, h:i A') . ($end ? ' until ' . $end->format('d M Y, h:i A') : '') . '.') . "\nCheck the official notice before you act.", $link, $source);
        }

        // --- deadline reminders: only the tightest threshold that applies (1 day beats 3 days)
        if ($open && $end && in_array($slug, self::CLOSES) && $end->gt($now)) {
            foreach (collect(config('cns.alerts.closing_thresholds_days'))->sort()->values() as $days) {
                if ($now->gte($end->copy()->subDays($days))) {
                    $this->raise('closing', $targetType, $targetId, 'ev:' . $e->id . ':close' . $days . ':' . $end->timestamp,
                        "$heading: $what closes in " . ($days === 1 ? '1 day' : "$days days"),
                        "$what closes on " . $end->format('d M Y, h:i A') . ($e->extended_end_at ? ' (extended)' : '') . ".\nDo not wait for the last hour.", $link, $source);
                    break;
                }
            }
        }
    }

    // ------------------------------------------------------------------ postponed / cancelled

    private function scanStatuses(): void
    {
        $cutoff = now()->subDays(config('cns.alerts.max_age_days'));
        $verified = fn ($m) => $m->last_verified_at && $m->last_verified_at->gte($m->updated_at);

        $cycles = CounsellingCycle::with('counselling')->where('is_public', true)
            ->whereHas('counselling', fn ($c) => $c->where('visibility', 'public')->where('is_active', true))->get();
        foreach ($cycles as $cycle) {
            if ($verified($cycle)) {
                $this->status('counselling_cycle', $cycle->id, $cycle->cycle_status, $cycle->updated_at, 'counselling', $cycle->counselling_id,
                    $cycle->counselling->name . ' ' . $cycle->title, 'counselling/' . $cycle->counselling->slug, $cycle->status_note, $cutoff, $cycle->source_url);
            }
            foreach ($cycle->rounds()->get() as $round) {
                if ($verified($round)) {
                    $this->status('counselling_round', $round->id, $round->round_status, $round->updated_at, 'counselling', $cycle->counselling_id,
                        $cycle->counselling->name . ' ' . $cycle->title . ' · ' . $round->name, 'counselling/' . $cycle->counselling->slug, $round->status_note, $cutoff, $round->source_url);
                }
            }
        }

        // Exam cycles have no verification stamp of their own: their dates are verified through their events.
        foreach (ExamCycle::with('exam')->where('is_public', true)->whereHas('exam', fn ($x) => $x->where('visibility', 'public')->where('status', true))->get() as $c) {
            $this->status('exam_cycle', $c->id, $c->cycle_status, $c->updated_at, 'exam', $c->exam_id, $c->exam->name . ' ' . $c->title, 'exams/' . $c->exam->slug, $c->status_note, $cutoff, null);
        }
    }

    private function status(string $subject, int $id, string $status, $updatedAt, string $targetType, int $targetId, string $heading, string $path, ?string $note, $cutoff, ?string $source): void
    {
        $prev = DB::table('cns_snapshots')->where('subject_type', $subject)->where('subject_id', $id)->value('state');
        $prevStatus = $prev ? (json_decode($prev, true)['status'] ?? null) : null;
        $bad = in_array($status, ['postponed', 'cancelled'], true);
        // Alert on a change INTO postponed/cancelled; or, the first time we look, if it is recent news.
        $changed = $prevStatus === null ? $updatedAt->gte($cutoff) : $prevStatus !== $status;

        if ($bad && $changed) {
            $this->raise('postponed', $targetType, $targetId, "status:$subject:$id:$status:" . $updatedAt->timestamp, "$heading $status",
                ucfirst($status) . ($note ? ": $note" : '.') . "\nWatch the official notice for new dates.", config('cns.frontend_url') . '/' . $path, \App\Models\ExamModule\CycleEvent::safeUrl($source));
        }
        $this->snapshot($subject, $id, ['status' => $status]);
    }

    // ------------------------------------------------------------------ seat matrix

    private function scanSeatMatrices(): void
    {
        $cutoff = now()->subDays(config('cns.alerts.max_age_days'));
        $versions = SeatMatrixVersion::with('round.cycle.counselling')->where('is_live', true)->where('version_no', '>', 1)->where('imported_at', '>=', $cutoff)->get();
        foreach ($versions as $v) {
            $cycle = $v->round?->cycle;
            $c = $cycle?->counselling;
            if (!$cycle || !$cycle->is_public || !$c || $c->visibility !== 'public' || !$c->is_active) continue;

            $older = SeatMatrixVersion::where('round_id', $v->round_id)->where('version_no', '<', $v->version_no)->orderByDesc('version_no')->first();
            $diff = $older ? $v->diffAgainst($older) : null;
            $this->raise('seats_revised', 'counselling', $c->id, 'seats:' . $v->id, $c->name . ' ' . $cycle->title . ' · ' . $v->round->name . ': seat matrix revised',
                'A new seat matrix is live' . ($v->label ? " ($v->label)" : '') . '.' . ($diff ? "\n{$diff['added']->count()} added, {$diff['removed']->count()} removed, {$diff['changed']->count()} changed." : ''),
                config('cns.frontend_url') . '/counselling/' . $c->slug, CycleEvent::safeUrl($v->source_url));
        }
    }

    // ------------------------------------------------------------------ helpers

    private function snapshot(string $type, int $id, array $state): void
    {
        DB::table('cns_snapshots')->updateOrInsert(['subject_type' => $type, 'subject_id' => $id], ['state' => json_encode($state), 'updated_at' => now()]);
    }

    /** Create the alert once, then queue it for everyone who follows this target, on the channels they confirmed. */
    private function raise(string $kind, string $targetType, int $targetId, string $key, string $title, string $body, string $link, ?string $source): ?Alert
    {
        $alert = Alert::firstOrCreate(['subject_key' => $key], [
            'kind' => $kind, 'target_type' => $targetType, 'target_id' => $targetId, 'title' => mb_substr($title, 0, 255),
            'body' => $body, 'link_url' => $link, 'source_url' => $source,
        ]);
        if (!$alert->wasRecentlyCreated) {
            return null;
        }
        $this->created++;

        $rows = [];
        $subs = Subscription::with('subscriber')->where('target_type', $targetType)->where('target_id', $targetId)->whereNotNull('confirmed_at')->get();
        foreach ($subs as $sub) {
            if (!$sub->wants($kind) || !($s = $sub->subscriber)) continue;
            foreach ([['email', $sub->via_email && $s->canEmail()], ['whatsapp', $sub->via_whatsapp && $s->canWhatsApp()], ['inapp', $sub->via_inapp && $s->canInApp()]] as [$channel, $ok]) {
                if ($ok) $rows[] = ['alert_id' => $alert->id, 'subscriber_id' => $s->id, 'channel' => $channel, 'status' => 'pending', 'created_at' => now()];
            }
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            AlertDelivery::insertOrIgnore($chunk);
        }
        return $alert;
    }
}
