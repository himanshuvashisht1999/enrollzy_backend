<?php

namespace App\Support\Calling;

use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The calling team's numbers for the role-wise dashboards, Live Monitor and Team Performance, worked out from what
 * the CRM already records:
 * - calls: calling_histories (updated_by = the staff member, user_id = the student, reason = calling_status,
 *   calling_action_id, date_required = the follow-up date). A call is "connected" when its status has is_connected.
 * - who owns a lead: the latest lead_assignments row per student.
 * - attendance and breaks: attendance / breaks (HR clock); leave: leaves; holidays: declared_holidays.
 * - targets: target_leads (calls and admissions per person per month); a team's target is the sum of its people.
 * - team: admin.manager_id (a team lead's team = everyone under them).
 * Live status is derived (there is no phone system): offline, on break, calling (a call logged in the last
 * idle-limit minutes) or idle.
 */
class CommandCenter
{
    public const TELECALLER_ROLES = ['Telle Caller', 'Telecalling'];
    public const TEAMLEAD_ROLES = ['Manager - Calling'];
    public const ADMIN_ROLES = ['admin', 'superadmin', 'CEO'];
    private const COLORS = ['#2563eb', '#7c3aed', '#db2777', '#0891b2', '#16a34a', '#d97706', '#dc2626', '#4f46e5', '#0f766e', '#9333ea'];

    private array $memo = [];

    // ------------------------------------------------------------------ who

    /** admin | teamlead | telecaller | null (everyone else keeps the general dashboard) */
    public function role(?Admin $u): ?string
    {
        if (!$u) return null;
        $r = (string) $u->role;
        if (in_array($r, self::TELECALLER_ROLES, true)) return 'telecaller';
        if (in_array($r, self::TEAMLEAD_ROLES, true)) return 'teamlead';
        if (in_array(strtolower($r), array_map('strtolower', self::ADMIN_ROLES), true)) return 'admin';
        return null;
    }

    /** every active member of the calling team (team leads and telecallers), keyed by id */
    public function callers(): Collection
    {
        return $this->memo['callers'] ??= Admin::whereIn('role', array_merge(self::TELECALLER_ROLES, self::TEAMLEAD_ROLES))
            ->where('status', 'active')->orderBy('name')->get(['id', 'name', 'role', 'manager_id', 'profile_image', 'department_id'])->keyBy('id');
    }

    /** the people a user sees: admin → whole calling team, team lead → themselves and everyone under them, telecaller → themselves */
    public function scope(Admin $u): array
    {
        $role = $this->role($u);
        if ($role === 'admin') return $this->callers()->keys()->all();
        if ($role === 'teamlead') return array_values(array_unique(array_merge([$u->id], array_intersect($this->below($u->id), $this->callers()->keys()->all()))));
        return [$u->id];
    }

    /** everyone under a person (recursive manager_id) */
    public function below(int $id): array
    {
        $all = $this->memo['managers'] ??= Admin::where('status', 'active')->pluck('manager_id', 'id')->all();
        $out = [];
        $walk = function ($boss) use (&$walk, &$out, $all) {
            foreach ($all as $id => $m) if ((int) $m === $boss && !in_array($id, $out, true)) { $out[] = $id; $walk($id); }
        };
        $walk($id);
        return $out;
    }

    /** a person for the views: name, role, initials, colour, photo */
    public function person(int $id): array
    {
        $a = $this->callers()[$id] ?? ($this->memo['people'][$id] ??= Admin::find($id, ['id', 'name', 'role', 'manager_id', 'profile_image']));
        $name = trim((string) ($a->name ?? 'Staff #' . $id));
        $parts = preg_split('/\s+/', $name);
        return ['id' => $id, 'name' => $name, 'first' => $parts[0], 'role' => $this->roleLabel($a->role ?? ''),
            'initials' => strtoupper(mb_substr($parts[0], 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : mb_substr($parts[0], 1, 1))),
            'color' => self::COLORS[$id % count(self::COLORS)], 'manager_id' => $a->manager_id ?? null];
    }

    public function roleLabel(string $role): string
    {
        return in_array($role, self::TELECALLER_ROLES, true) ? 'Telecaller' : (in_array($role, self::TEAMLEAD_ROLES, true) ? 'Team Lead' : ucfirst($role));
    }

    // ------------------------------------------------------------------ rules and masters

    public function rules(): object
    {
        return $this->memo['rules'] ??= (object) array_merge(
            ['calling_shift_start' => '10:00', 'calling_grace_min' => 15, 'calling_idle_min' => 30, 'calling_break_min' => 45, 'calling_overdue_max' => 15, 'calling_daily_target' => 60],
            array_filter((array) (rescue(fn () => DB::table('settings')->first(['calling_shift_start', 'calling_grace_min', 'calling_idle_min', 'calling_break_min', 'calling_overdue_max', 'calling_daily_target']), null, false) ?? []), fn ($v) => $v !== null));
    }

    /** ids of the statuses / actions the numbers count */
    public function codes(): array
    {
        return $this->memo['codes'] ??= (function () {
            $st = DB::table('calling_status')->whereNull('deleted_at')->get(['id', 'name', 'is_connected']);
            $ac = DB::table('calling_actions')->get(['id', 'name']);
            $like = fn ($rows, $needle) => $rows->filter(fn ($r) => stripos($r->name, $needle) !== false)->pluck('id')->map(fn ($i) => (int) $i)->all();
            return [
                'connected' => $st->where('is_connected', 1)->pluck('id')->map(fn ($i) => (int) $i)->all(),
                // an admission is the ADMISSION DONE action; the status "Admission Taken" means the student already
                // took admission elsewhere (a lost lead), so it is not counted as one
                'positive' => $like($st, 'positive'),
                'application' => $like($ac, 'application done'), 'admission' => $like($ac, 'admission done'),
                'status_names' => $st->pluck('name', 'id')->map(fn ($n) => ucwords(strtolower($n)))->all(),
            ];
        })();
    }

    // ------------------------------------------------------------------ ranges

    public const RANGES = ['today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This week', 'month' => 'This month'];

    /** [from, to) for a range name */
    public function range(string $r): array
    {
        $t = Carbon::today();
        return match ($r) {
            'yesterday' => [$t->copy()->subDay(), $t],
            'week' => [$t->copy()->startOfWeek(), now()->addSecond()],
            'month' => [$t->copy()->startOfMonth(), now()->addSecond()],
            default => [$t, now()->addSecond()],
        };
    }

    // ------------------------------------------------------------------ calls

    /** per person: calls, conn, uniq, pos, fups, apps, adm (zeros for people with no calls) */
    public function metrics(array $ids, Carbon $from, Carbon $to): array
    {
        $c = $this->codes();
        $in = fn ($list) => $list ? implode(',', array_map('intval', $list)) : '0';
        $rows = $ids ? DB::table('calling_histories')->whereIn('updated_by', array_map('strval', $ids))->where('created_at', '>=', $from)->where('created_at', '<', $to)
            ->groupBy('updated_by')->selectRaw("updated_by AS id, COUNT(*) AS calls, COUNT(DISTINCT user_id) AS uniq,
                SUM(reason IN ({$in($c['connected'])})) AS conn, SUM(reason IN ({$in($c['positive'])})) AS pos,
                SUM(date_required IS NOT NULL AND date_required <> '') AS fups,
                SUM(calling_action_id IN ({$in($c['application'])})) AS apps,
                SUM(calling_action_id IN ({$in($c['admission'])})) AS adm")->get()->keyBy('id') : collect();
        $out = [];
        foreach ($ids as $id) {
            $r = $rows[(string) $id] ?? null;
            $out[$id] = ['calls' => (int) ($r->calls ?? 0), 'conn' => (int) ($r->conn ?? 0), 'uniq' => (int) ($r->uniq ?? 0), 'pos' => (int) ($r->pos ?? 0),
                'fups' => (int) ($r->fups ?? 0), 'apps' => (int) ($r->apps ?? 0), 'adm' => (int) ($r->adm ?? 0)];
        }
        return $out;
    }

    public function sum(array $metrics): array
    {
        $t = ['calls' => 0, 'conn' => 0, 'uniq' => 0, 'pos' => 0, 'fups' => 0, 'apps' => 0, 'adm' => 0];
        foreach ($metrics as $m) foreach ($t as $k => $_) $t[$k] += $m[$k];
        return $t;
    }

    public static function pct($a, $b): int
    {
        return $b ? (int) round($a * 100 / $b) : 0;
    }

    /**
     * per person, the leads they own now: overdue / due today follow-ups (the latest call since the lead was
     * assigned to them has a follow-up date) and fresh leads (not called since assignment)
     */
    public function queues(array $ids): array
    {
        $out = array_fill_keys($ids, ['overdue' => 0, 'today' => 0, 'fresh' => 0, 'owned' => 0]);
        if (!$ids) return $out;
        $today = Carbon::today()->toDateString();
        $rows = DB::table('lead_assignments as la')
            ->joinSub(DB::table('lead_assignments')->selectRaw('customer_id, MAX(id) AS mid')->groupBy('customer_id'), 'cur', 'cur.mid', '=', 'la.id')
            ->leftJoinSub(DB::table('calling_histories')->selectRaw('user_id, MAX(id) AS cid')->groupBy('user_id'), 'lc', 'lc.user_id', '=', 'la.customer_id')
            ->leftJoin('calling_histories as ch', fn ($j) => $j->on('ch.id', '=', 'lc.cid')->whereColumn('ch.created_at', '>=', 'la.updated_at'))
            ->whereIn('la.staff_id', $ids)
            ->get(['la.staff_id', 'ch.id as call_id', 'ch.date_required']);
        foreach ($rows as $r) {
            $o = &$out[$r->staff_id];
            $o['owned']++;
            if (!$r->call_id) $o['fresh']++;
            elseif ($r->date_required && $r->date_required < $today) $o['overdue']++;
            elseif ($r->date_required === $today) $o['today']++;
            unset($o);
        }
        return $out;
    }

    /** calls per hour today, per person: [hours => [..], series => [id => [count per hour]]] */
    public function hourly(array $ids): array
    {
        $now = now();
        $hours = range(max(8, min($now->hour, 20) - 9), max(17, min($now->hour, 21)));
        $rows = $ids ? DB::table('calling_histories')->whereIn('updated_by', array_map('strval', $ids))->where('created_at', '>=', Carbon::today())
            ->groupBy('updated_by', DB::raw('HOUR(created_at)'))->selectRaw('updated_by, HOUR(created_at) AS h, COUNT(*) AS n')->get() : collect();
        $series = [];
        foreach ($ids as $id) $series[$id] = array_map(fn ($h) => (int) ($rows->first(fn ($r) => (int) $r->updated_by === $id && (int) $r->h === $h)->n ?? 0), $hours);
        return ['hours' => $hours, 'series' => $series];
    }

    /** calls by status: [[name, count], ...] biggest first, the rest as "Other" */
    public function disposition(array $ids, Carbon $from, Carbon $to, int $top = 6): array
    {
        $names = $this->codes()['status_names'];
        $rows = $ids ? DB::table('calling_histories')->whereIn('updated_by', array_map('strval', $ids))->where('created_at', '>=', $from)->where('created_at', '<', $to)
            ->groupBy('reason')->selectRaw('reason, COUNT(*) AS n')->orderByDesc('n')->get() : collect();
        $list = $rows->map(fn ($r) => [$names[$r->reason] ?? 'No status', (int) $r->n])->values();
        $head = $list->take($top)->all();
        if ($list->count() > $top) $head[] = ['Other', $list->slice($top)->sum(1)];
        return $head;
    }

    /** the leads owned by these people now, step by step */
    public function funnel(array $ids): array
    {
        if (!$ids) return [];
        $c = $this->codes();
        $owned = DB::table('lead_assignments as la')
            ->joinSub(DB::table('lead_assignments')->selectRaw('customer_id, MAX(id) AS mid')->groupBy('customer_id'), 'cur', 'cur.mid', '=', 'la.id')
            ->whereIn('la.staff_id', $ids);
        $calls = fn () => DB::table('calling_histories as ch')->joinSub((clone $owned)->select('la.customer_id', 'la.updated_at'), 'o', 'o.customer_id', '=', 'ch.user_id')
            ->whereColumn('ch.created_at', '>=', 'o.updated_at');
        $hot = DB::table('lead_qualities')->where('name', 'like', '%hot%')->pluck('id')->all();
        return [
            ['Allocated', (clone $owned)->count()],
            ['Attempted', $calls()->distinct()->count('ch.user_id')],
            ['Connected at least once', $calls()->whereIn('ch.reason', array_map('strval', $c['connected'] ?: [0]))->distinct()->count('ch.user_id')],
            ['Hot', $hot ? (clone $owned)->join('users as u', 'u.id', '=', 'la.customer_id')->whereIn('u.lead_quality_id', $hot)->count() : 0],
            ['Application', $calls()->whereIn('ch.calling_action_id', $c['application'] ?: [0])->distinct()->count('ch.user_id')],
            ['Admission', $calls()->whereIn('ch.calling_action_id', $c['admission'] ?: [0])->distinct()->count('ch.user_id')],
        ];
    }

    // ------------------------------------------------------------------ attendance and live status

    /**
     * per person, today: code (W working, OUT punched out, NP not punched in, L on leave, WO week-off / holiday),
     * label, late, in, out, live status (calling | idle | break | offline), since, the open break, breaks today,
     * the last call (lead, status, at)
     */
    public function live(array $ids): array
    {
        if (!$ids) return [];
        $rules = $this->rules();
        $today = Carbon::today();
        $att = DB::table('attendance')->whereIn('staff_id', $ids)->whereDate('date', $today)->orderBy('id')->get()->groupBy('staff_id');
        $breaks = DB::table('breaks')->whereIn('staff_id', $ids)->where('start', '>=', $today)->get()->groupBy('staff_id');
        $onLeave = $this->onLeave($ids, $today);
        $holiday = $this->holidayOn($today);
        $names = $this->codes()['status_names'];
        // each person's latest call today (by time, not id)
        $last = DB::table('calling_histories as ch')->joinSub(DB::table('calling_histories')->whereIn('updated_by', array_map('strval', $ids))->where('created_at', '>=', $today)
            ->groupBy('updated_by')->selectRaw('updated_by, MAX(created_at) AS at'), 'l', fn ($j) => $j->on('l.updated_by', '=', 'ch.updated_by')->on('l.at', '=', 'ch.created_at'))
            ->orderBy('ch.id')->get(['ch.updated_by', 'ch.user_name', 'ch.reason', 'ch.created_at'])->keyBy('updated_by');
        $lateAfter = Carbon::parse($today->toDateString() . ' ' . $rules->calling_shift_start)->addMinutes((int) $rules->calling_grace_min);

        $out = [];
        foreach ($ids as $id) {
            $a = $att[$id] ?? collect();
            $first = $a->first();
            $open = $a->first(fn ($r) => !$r->check_out);
            $in = $first?->check_in ? Carbon::parse($first->check_in) : null;
            $outAt = !$open && $a->isNotEmpty() ? Carbon::parse($a->last()->check_out) : null;
            $bs = $breaks[$id] ?? collect();
            $brk = $bs->first(fn ($b) => !$b->end);
            $breakSec = $bs->sum(fn ($b) => $b->end ? Carbon::parse($b->start)->diffInSeconds(Carbon::parse($b->end)) : Carbon::parse($b->start)->diffInSeconds(now()));
            $lc = $last[(string) $id] ?? null;
            $lcAt = $lc ? Carbon::parse($lc->created_at) : null;

            if (!$in) [$code, $label] = isset($onLeave[$id]) ? ['L', 'On leave'] : ($holiday || $today->isSunday() ? ['WO', $holiday ?: 'Week-off'] : ['NP', 'Not punched in']);
            elseif ($open) [$code, $label] = ['W', 'Working'];
            else [$code, $label] = ['OUT', 'Punched out'];

            if (!$open) { $st = 'offline'; $since = $outAt ?? $today; }
            elseif ($brk) { $st = 'break'; $since = Carbon::parse($brk->start); }
            elseif ($lcAt && $lcAt->diffInMinutes(now()) < (int) $rules->calling_idle_min) { $st = 'calling'; $since = $lcAt; }
            else { $st = 'idle'; $since = max($lcAt ?? $in, $bs->max(fn ($b) => $b->end ? Carbon::parse($b->end) : null) ?? $in); }

            $out[$id] = ['code' => $code, 'label' => $label, 'late' => $in && $in->gt($lateAfter), 'in' => $in, 'out' => $outAt,
                'status' => $st, 'since' => $since, 'break' => $brk ? ['type' => $brk->type, 'since' => Carbon::parse($brk->start)] : null,
                'breaks' => $bs->count(), 'break_sec' => (int) $breakSec,
                'last' => $lc ? ['lead' => $lc->user_name ?: 'Lead', 'status' => $names[$lc->reason] ?? '—', 'at' => $lcAt] : null,
                'offline_reason' => !$open ? ($outAt ? 'Punched out ' . $outAt->format('g:i A') : $label) : null];
        }
        return $out;
    }

    /** this month so far: days present, late, on leave, absent (working days before today with no punch and no leave) */
    public function monthAttendance(int $id): array
    {
        $rules = $this->rules();
        $a = Carbon::today()->startOfMonth();
        $rows = DB::table('attendance')->where('staff_id', $id)->whereDate('date', '>=', $a)->whereNotNull('check_in')->get(['date', 'check_in']);
        $days = $rows->groupBy(fn ($r) => substr($r->date, 0, 10));
        $late = $days->filter(fn ($g, $d) => Carbon::parse($g->min('check_in'))->gt(Carbon::parse("$d {$rules->calling_shift_start}")->addMinutes((int) $rules->calling_grace_min)))->count();
        $leave = 0;
        foreach (DB::table('leaves')->where('staff_id', $id)->whereRaw('LOWER(status) = ?', ['approved'])->whereDate('date_till', '>=', $a)->whereDate('date_from', '<=', Carbon::today())->get(['date_from', 'date_till']) as $l) {
            $leave += $this->workingDays(max(Carbon::parse($l->date_from), $a), min(Carbon::parse($l->date_till)->addDay(), Carbon::today()->addDay()), $id);
        }
        $worked = $days->keys()->filter(fn ($d) => $d < Carbon::today()->toDateString())->count();
        return ['present' => $days->count(), 'late' => $late, 'leave' => $leave,
            'absent' => max(0, $this->workingDays($a, Carbon::today(), $id) - $worked - $leave)];
    }

    /** ids of people on approved leave on a day */
    public function onLeave(array $ids, Carbon $day): array
    {
        return DB::table('leaves')->whereIn('staff_id', $ids)->whereRaw('LOWER(status) = ?', ['approved'])
            ->whereDate('date_from', '<=', $day)->whereDate('date_till', '>=', $day)->pluck('staff_id', 'staff_id')->all();
    }

    /** the holiday's name on a day (one for everyone or for this person), or null */
    public function holidayOn(Carbon $day, ?int $staffId = null): ?string
    {
        return $this->holidays($staffId)->first(fn ($h) => $h->date === $day->toDateString())?->name;
    }

    /** holidays that apply to everyone, or to this person (by id or department) */
    public function holidays(?int $staffId = null): Collection
    {
        $dept = $staffId ? ($this->callers()[$staffId]->department_id ?? null) : null;
        return $this->memo['holidays'][$staffId ?? 0] ??= DB::table('declared_holidays')->orderBy('date')->get(['name', 'date', 'department_ids', 'staff_ids'])
            ->filter(function ($h) use ($staffId, $dept) {
                $staff = array_filter(explode(',', (string) $h->staff_ids));
                $depts = array_filter(explode(',', (string) $h->department_ids));
                if (!$staff && !$depts) return true;
                return $staffId && (in_array((string) $staffId, $staff, true) || ($dept && in_array((string) $dept, $depts, true)));
            })->values();
    }

    /** working days (not Sunday, not a holiday) in [from, to) */
    public function workingDays(Carbon $from, Carbon $to, ?int $staffId = null): int
    {
        $off = $this->holidays($staffId)->pluck('date')->flip();
        $n = 0;
        for ($d = $from->copy()->startOfDay(); $d->lt($to); $d->addDay()) if (!$d->isSunday() && !isset($off[$d->toDateString()])) $n++;
        return $n;
    }

    // ------------------------------------------------------------------ targets

    /** a month's target for these people (summed): ['calls', 'adm', 'people' => n with a target] or null */
    public function target(array $ids, ?Carbon $month = null): ?array
    {
        $m = $month ?? Carbon::today();
        $rows = DB::table('target_leads')->whereIn('staff_id', $ids)->where('year', $m->year)->where('month', $m->format('F'))->get();
        return $rows->isEmpty() ? null : ['calls' => (int) $rows->sum('month_target_calling'), 'adm' => (int) $rows->sum('month_target_admissions'), 'people' => $rows->count()];
    }

    /**
     * the month's progress: done (calls, adm), the target, what is needed per working day from today to finish on
     * time, what should be done by today pro-rata, and whether that is on track
     */
    public function progress(array $ids, ?int $staffForHolidays = null): array
    {
        $a = Carbon::today()->startOfMonth();
        $b = $a->copy()->addMonth();
        $today = Carbon::today();
        $done = $this->sum($this->metrics($ids, $a, now()->addSecond()));
        $before = $this->sum($this->metrics($ids, $a, $today))['calls'];
        $total = $this->workingDays($a, $b, $staffForHolidays);
        $elapsed = $this->workingDays($a, $today, $staffForHolidays);
        $left = max(1, $this->workingDays($today, $b, $staffForHolidays));
        $t = $this->target($ids);
        $p = ['t' => $t, 'done' => $done, 'month' => $a->format('F Y'), 'total' => $total, 'left' => $left];
        if (!$t) return $p;
        $expected = (int) round($t['calls'] * $elapsed / max(1, $total));
        return $p + ['daily' => max(0, (int) ceil(($t['calls'] - $before) / $left)), 'expected' => $expected,
            'behind' => max(0, $expected - $before), 'on_track' => $before >= $expected * 0.95,
            'calls_left' => max(0, $t['calls'] - $done['calls']), 'adm_left' => max(0, $t['adm'] - $done['adm'])];
    }

    /** calls a person should make today: from their monthly target, else the rule's default */
    public function dailyTarget(int $id): int
    {
        $p = $this->progress([$id], $id);
        return $p['t'] ? max(1, $p['daily']) : (int) $this->rules()->calling_daily_target;
    }

    // ------------------------------------------------------------------ leave

    /** leave requests waiting, from these people */
    public function pendingLeaves(array $ids): int
    {
        return $ids ? DB::table('leaves')->whereIn('staff_id', $ids)->whereRaw('LOWER(status) = ?', ['pending'])->count() : 0;
    }

    /** this year's leave left per leave type (yearly quota minus approved days) and pending requests */
    public function leaveBalance(int $id): array
    {
        $y = Carbon::today()->year;
        $types = DB::table('leave_settings')->whereNull('deleted_at')->where('yearly_leave', '>', 0)->orderBy('id')->get(['id', 'name', 'yearly_leave']);
        $taken = DB::table('leaves')->where('staff_id', $id)->whereRaw('LOWER(status) = ?', ['approved'])->whereYear('date_from', $y)->get(['leave_type_id', 'date_from', 'date_till'])
            ->groupBy('leave_type_id')->map(fn ($g) => $g->sum(fn ($l) => $this->workingDays(Carbon::parse($l->date_from), Carbon::parse($l->date_till)->addDay(), $id)));
        return ['types' => $types->map(fn ($t) => ['name' => $t->name, 'left' => max(0, (int) $t->yearly_leave - (int) ($taken[(string) $t->id] ?? 0))])->take(3)->all(),
            'pending' => DB::table('leaves')->where('staff_id', $id)->whereRaw('LOWER(status) = ?', ['pending'])->count()];
    }

    /** the next holidays for a person */
    public function upcomingHolidays(int $id, int $n = 3): array
    {
        $t = Carbon::today();
        return $this->holidays($id)->filter(fn ($h) => $h->date >= $t->toDateString())->take($n)
            ->map(fn ($h) => ['name' => $h->name, 'date' => Carbon::parse($h->date), 'in' => (int) $t->diffInDays(Carbon::parse($h->date))])->values()->all();
    }

    // ------------------------------------------------------------------ alerts and feed

    /** [level warn|bad|info, text, link or null] for a viewer over their people */
    public function alerts(Admin $viewer, array $ids, array $live, array $queues): array
    {
        $r = $this->rules();
        $out = [];
        $now = now();
        $graceOver = $now->gt(Carbon::parse(Carbon::today()->toDateString() . ' ' . $r->calling_shift_start)->addMinutes((int) $r->calling_grace_min));
        $workday = !Carbon::today()->isSunday() && !$this->holidayOn(Carbon::today());
        $month = Carbon::today()->format('F');
        foreach ($ids as $id) {
            if ($id === $viewer->id) continue;
            $p = $this->person($id);
            $l = $live[$id];
            $mins = $l['since'] ? (int) $l['since']->diffInMinutes($now) : 0;
            if ($workday && $graceOver && $l['code'] === 'NP') $out[] = ['warn', "{$p['name']} has not punched in (shift {$r->calling_shift_start})", route('admin.hr.attendance.index')];
            if ($l['status'] === 'idle' && $mins > (int) $r->calling_idle_min) $out[] = ['warn', "{$p['name']} has logged no call for " . self::hm($mins * 60), null];
            if ($l['status'] === 'break' && $mins > (int) $r->calling_break_min) $out[] = ['warn', "{$p['name']} on " . ($l['break']['type'] ?? '') . " break for " . self::hm($mins * 60), null];
            if (($queues[$id]['overdue'] ?? 0) > (int) $r->calling_overdue_max) $out[] = ['bad', "{$p['name']} has {$queues[$id]['overdue']} overdue follow-ups (limit {$r->calling_overdue_max})", route('admin.students-crm.calling-dashboard.index')];
            if (!$this->target([$id])) $out[] = ['bad', "No {$month} target set for {$p['name']}", route('admin.target-leads.index')];
        }
        // open escalations (LeadFlags), per team lead they went to
        foreach (DB::table('lead_escalations')->where('status', 'open')->whereIn('raised_by', $ids)->selectRaw('team_lead_id, COUNT(*) AS n, MIN(created_at) AS since')->groupBy('team_lead_id')->get() as $e) {
            $mine = $e->team_lead_id == $viewer->id;
            $who = $mine ? 'you' : ($e->team_lead_id ? $this->person((int) $e->team_lead_id)['name'] : 'no one (the caller has no manager set)');
            $out[] = ['bad', "{$e->n} open " . ($e->n == 1 ? 'escalation' : 'escalations') . " waiting for {$who}, oldest " . Carbon::parse($e->since)->diffForHumans(), $mine ? route('admin.dashboard') . '#escalations' : null];
        }
        $pa = $this->pendingLeaves(array_diff($ids, [$viewer->id]));
        if ($pa) $out[] = ['info', "{$pa} leave " . ($pa === 1 ? 'request' : 'requests') . ' waiting for approval', route('admin.hr.leaves.index')];
        return $out;
    }

    /** today's calls, punches and breaks of these people, newest first */
    public function feed(array $ids, int $n = 40): array
    {
        if (!$ids) return [];
        $names = $this->codes()['status_names'];
        $today = Carbon::today();
        $items = DB::table('calling_histories')->whereIn('updated_by', array_map('strval', $ids))->where('created_at', '>=', $today)->orderByDesc('created_at')->limit($n)
            ->get(['updated_by', 'user_name', 'reason', 'created_at'])
            ->map(fn ($c) => ['t' => Carbon::parse($c->created_at), 'id' => (int) $c->updated_by, 'html' => 'called <b>' . e($c->user_name ?: 'a lead') . '</b> · ' . e($names[$c->reason] ?? '—')]);
        foreach (DB::table('attendance')->whereIn('staff_id', $ids)->whereDate('date', $today)->get() as $a) {
            if ($a->check_in) $items->push(['t' => Carbon::parse($a->check_in), 'id' => (int) $a->staff_id, 'html' => 'punched in' . ($a->work_from ? ' · ' . e(ucfirst($a->work_from)) : '')]);
            if ($a->check_out) $items->push(['t' => Carbon::parse($a->check_out), 'id' => (int) $a->staff_id, 'html' => 'punched out']);
        }
        foreach (DB::table('breaks')->whereIn('staff_id', $ids)->where('start', '>=', $today)->get() as $b) {
            $items->push(['t' => Carbon::parse($b->start), 'id' => (int) $b->staff_id, 'html' => 'started a ' . e($b->type) . ' break']);
            if ($b->end) $items->push(['t' => Carbon::parse($b->end), 'id' => (int) $b->staff_id, 'html' => 'back from break']);
        }
        return $items->sortByDesc('t')->take($n)->values()->all();
    }

    // ------------------------------------------------------------------ formatting

    public static function hm(int $sec): string
    {
        $h = intdiv(max(0, $sec), 3600);
        $m = intdiv(max(0, $sec) % 3600, 60);
        return $h ? "{$h}h {$m}m" : "{$m}m";
    }
}
