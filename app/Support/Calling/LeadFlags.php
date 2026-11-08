<?php

namespace App\Support\Calling;

use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Escalations and important leads (tables lead_escalations, important_leads), set from the call form:
 * - escalate: the lead goes to the caller's manager (their team lead) with a reason; one open escalation per lead.
 *   It closes when that team lead (or anyone above) logs a call on the lead, or when they resolve it.
 * - important: a flag on the lead for the whole team; the call form can set or clear it.
 * Team leads see both on their dashboard (CommandCenterController), admins see the open escalations in alerts.
 */
class LeadFlags
{
    public function __construct(private CommandCenter $cc)
    {
    }

    /** whether a lead is marked important (the lists' star); one query per request, the table is small */
    public static function isImportant($customerId): bool
    {
        $bag = request()->attributes;
        if (!$bag->has('important_lead_ids')) $bag->set('important_lead_ids', array_flip(DB::table('important_leads')->pluck('customer_id')->all()));
        return isset($bag->get('important_lead_ids')[(int) $customerId]);
    }

    /** the state the call form shows for a lead */
    public function state(int $customerId): array
    {
        $esc = DB::table('lead_escalations')->where('customer_id', $customerId)->where('status', 'open')->latest('id')->first();
        return [
            'important' => DB::table('important_leads')->where('customer_id', $customerId)->exists(),
            'escalation' => $esc ? ['to' => $esc->team_lead_id ? $this->cc->person((int) $esc->team_lead_id)['name'] : 'your manager',
                'by' => $this->cc->person((int) $esc->raised_by)['name'], 'reason' => $esc->reason, 'at' => Carbon::parse($esc->created_at)->diffForHumans()] : null,
        ];
    }

    /**
     * after a call is saved (CallingController::store): set / clear "important", raise an escalation, and close the
     * lead's open escalation when its team lead (or someone above them) made the call. Returns a note for the message.
     */
    public function afterCall(Request $request, int $customerId, Admin $caller): ?string
    {
        $notes = [];
        if ($request->has('lead_important')) {
            if ($request->boolean('lead_important')) {
                $added = DB::table('important_leads')->insertOrIgnore(['customer_id' => $customerId, 'marked_by' => $caller->id, 'created_at' => now(), 'updated_at' => now()]);
                if ($added) { $notes[] = 'marked important'; $this->log($customerId, $caller->id, 'marked_important', 'Marked the lead important'); }
            } elseif (DB::table('important_leads')->where('customer_id', $customerId)->delete()) {
                $notes[] = 'no longer important';
                $this->log($customerId, $caller->id, 'unmarked_important', 'Removed the important flag');
            }
        }

        // the team lead (or anyone above) calling the lead closes its open escalation
        $open = DB::table('lead_escalations')->where('customer_id', $customerId)->where('status', 'open')->get();
        foreach ($open as $e) {
            if ($e->raised_by != $caller->id && ($e->team_lead_id == $caller->id || in_array((int) $e->raised_by, $this->cc->below($caller->id), true))) {
                DB::table('lead_escalations')->where('id', $e->id)->update(['status' => 'resolved', 'resolved_by' => $caller->id, 'resolved_at' => now(),
                    'resolution' => 'Called the lead' . ($request->filled('remark') ? ': ' . mb_substr(strip_tags((string) $request->remark), 0, 400) : ''), 'updated_at' => now()]);
                $notes[] = 'escalation closed';
                $this->log($customerId, $caller->id, 'escalation_resolved', 'Called the escalated lead');
            }
        }

        if ($request->boolean('lead_escalate')) {
            $reason = mb_substr(trim(strip_tags((string) $request->input('escalation_reason'))), 0, 500);
            if ($reason === '') return trim(implode(', ', $notes) . '; escalation not sent: add a reason', '; ');
            $to = $caller->manager_id ? (int) $caller->manager_id : null;
            $still = DB::table('lead_escalations')->where('customer_id', $customerId)->where('status', 'open')->exists();
            if (!$still) {
                DB::table('lead_escalations')->insert(['customer_id' => $customerId, 'raised_by' => $caller->id, 'team_lead_id' => $to, 'reason' => $reason, 'status' => 'open', 'created_at' => now(), 'updated_at' => now()]);
                $notes[] = 'escalated to ' . ($to ? $this->cc->person($to)['name'] : 'your manager');
                $this->log($customerId, $caller->id, 'escalated', 'Escalated: ' . $reason);
            } else {
                $notes[] = 'already escalated';
            }
        }
        return $notes ? implode(', ', $notes) : null;
    }

    /** close an escalation without a call (the team lead it went to, someone above them, or an admin) */
    public function resolve(int $id, Admin $by, ?string $note): bool
    {
        $e = DB::table('lead_escalations')->where('id', $id)->where('status', 'open')->first();
        if (!$e || !$this->canResolve($by, $e)) return false;
        DB::table('lead_escalations')->where('id', $id)->update(['status' => 'resolved', 'resolved_by' => $by->id, 'resolved_at' => now(),
            'resolution' => mb_substr(trim((string) $note), 0, 500) ?: 'Resolved', 'updated_at' => now()]);
        $this->log((int) $e->customer_id, $by->id, 'escalation_resolved', 'Resolved the escalation' . ($note ? ': ' . $note : ''));
        return true;
    }

    public function canResolve(Admin $by, object $e): bool
    {
        return $this->cc->role($by) === 'admin' || $e->team_lead_id == $by->id || in_array((int) $e->raised_by, $this->cc->below($by->id), true);
    }

    /** open escalations sent to a person (or, for an admin, from anyone in their scope), newest first */
    public function openFor(Admin $u): Collection
    {
        $q = DB::table('lead_escalations as e')->leftJoin('users as c', 'c.id', '=', 'e.customer_id')->where('e.status', 'open');
        $this->cc->role($u) === 'admin' ? $q->whereIn('e.raised_by', $this->cc->scope($u)) : $q->where('e.team_lead_id', $u->id);
        return $q->orderByDesc('e.id')->limit(50)->get(['e.*', 'c.name as lead', 'c.phone', 'c.mobile']);
    }

    /** escalations a person raised that are still open */
    public function raisedBy(int $id): Collection
    {
        return DB::table('lead_escalations as e')->leftJoin('users as c', 'c.id', '=', 'e.customer_id')->where('e.status', 'open')->where('e.raised_by', $id)
            ->orderByDesc('e.id')->limit(20)->get(['e.*', 'c.name as lead']);
    }

    /** the team's important leads (owned by these people now) */
    public function importantFor(array $ids, int $limit = 50): Collection
    {
        if (!$ids) return collect();
        return DB::table('important_leads as i')->join('users as c', 'c.id', '=', 'i.customer_id')
            ->joinSub(DB::table('lead_assignments')->selectRaw('customer_id, MAX(id) AS mid')->groupBy('customer_id'), 'cur', 'cur.customer_id', '=', 'i.customer_id')
            ->join('lead_assignments as la', 'la.id', '=', 'cur.mid')->whereIn('la.staff_id', $ids)
            ->orderByDesc('i.id')->limit($limit)->get(['i.customer_id', 'i.marked_by', 'i.created_at', 'c.name as lead', 'c.phone', 'la.staff_id as owner']);
    }

    private function log(int $customerId, int $adminId, string $type, string $text): void
    {
        DB::table('lead_activity_logs')->insert(['customer_id' => $customerId, 'admin_id' => $adminId, 'action_type' => $type, 'description' => mb_substr($text, 0, 500),
            'properties' => json_encode([]), 'created_at' => now(), 'updated_at' => now()]);
    }
}
