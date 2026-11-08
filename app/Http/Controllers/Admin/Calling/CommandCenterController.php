<?php

namespace App\Http\Controllers\Admin\Calling;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Support\Calling\CommandCenter;
use App\Support\Calling\LeadFlags;
use App\Support\Calling\Notices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The calling team's command center (design: enrollzy-calling-command-center). Home page by role:
 * admin → Live Monitor, team lead → their dashboard (team target, approvals, team attendance), telecaller → their
 * dashboard (target, calls today, follow-ups, attendance, leave). Admins and team leads also get Team Performance.
 * All numbers come from App\Support\Calling\CommandCenter.
 */
class CommandCenterController extends Controller
{
    public function __construct(private CommandCenter $cc, private Notices $notices)
    {
    }

    /** the home page for a calling-team role (DashboardController hands over) */
    public function home(Request $request)
    {
        $u = $request->user();
        return match ($this->cc->role($u)) {
            'admin' => $this->monitor($request),
            'teamlead' => $this->teamLead($u),
            default => $this->telecaller($u),
        };
    }

    public function monitor(Request $request)
    {
        $u = $request->user();
        $this->only($u, ['admin', 'teamlead']);
        $range = array_key_exists($request->query('range'), CommandCenter::RANGES) ? $request->query('range') : 'today';
        [$from, $to] = $this->cc->range($range);
        $ids = $this->cc->scope($u);
        $m = $this->cc->metrics($ids, $from, $to);
        $today = $this->cc->metrics($ids, ...$this->cc->range('today'));
        $live = $this->cc->live($ids);
        $queues = $this->cc->queues($ids);
        return view('admin.calling.monitor', $this->shared($u) + [
            'range' => $range, 'ids' => $ids, 'm' => $m, 'tot' => $this->cc->sum($m), 'today' => $today, 'live' => $live, 'queues' => $queues,
            'daily' => collect($ids)->mapWithKeys(fn ($id) => [$id => $this->cc->dailyTarget($id)])->all(),
            'progress' => $this->cc->progress($ids), 'alerts' => $this->cc->alerts($u, $ids, $live, $queues), 'feed' => $this->cc->feed($ids),
            'pending' => $this->cc->pendingLeaves(array_diff($ids, [$u->id])), 'rules' => $this->cc->rules(),
        ]);
    }

    public function performance(Request $request)
    {
        $u = $request->user();
        $this->only($u, ['admin', 'teamlead']);
        $range = array_key_exists($request->query('range'), CommandCenter::RANGES) ? $request->query('range') : 'today';
        [$from, $to] = $this->cc->range($range);
        $ids = $this->cc->scope($u);
        $m = $this->cc->metrics($ids, $from, $to);
        $mm = $this->cc->metrics($ids, ...$this->cc->range('month'));
        $queues = $this->cc->queues($ids);
        $rows = collect($ids)->map(fn ($id) => ['id' => $id, 'p' => $this->cc->person($id), 'm' => $m[$id], 'mm' => $mm[$id], 'q' => $queues[$id], 't' => $this->cc->target([$id])]);

        if ($request->query('export') === 'csv') {
            $csv = fopen('php://temp', 'r+');
            fputcsv($csv, ['Person', 'Role', 'Calls', 'Unique leads', 'Connected', 'Connect %', 'Positive', 'Follow-ups set', 'Applications', 'Admissions', 'Overdue follow-ups', 'Month calls / target', 'Month admissions / target']);
            foreach ($rows as $r) fputcsv($csv, [$r['p']['name'], $r['p']['role'], $r['m']['calls'], $r['m']['uniq'], $r['m']['conn'], CommandCenter::pct($r['m']['conn'], $r['m']['calls']),
                $r['m']['pos'], $r['m']['fups'], $r['m']['apps'], $r['m']['adm'], $r['q']['overdue'], $r['mm']['calls'] . ' / ' . ($r['t']['calls'] ?? '-'), $r['mm']['adm'] . ' / ' . ($r['t']['adm'] ?? '-')]);
            rewind($csv);
            return response(stream_get_contents($csv), 200, ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="team-performance-' . $range . '-' . now()->format('Y-m-d') . '.csv"']);
        }

        return view('admin.calling.performance', $this->shared($u) + [
            'range' => $range, 'ids' => $ids, 'rows' => $rows, 'tot' => $this->cc->sum($m), 'totM' => $this->cc->sum($mm), 'teamTarget' => $this->cc->target($ids),
            'hourly' => $this->cc->hourly($ids), 'disposition' => $this->cc->disposition($ids, $from, $to), 'funnel' => $this->cc->funnel($ids),
            'leaders' => $rows->sortBy([fn ($a, $b) => $b['mm']['adm'] <=> $a['mm']['adm'], fn ($a, $b) => $b['mm']['conn'] <=> $a['mm']['conn']])->values(),
        ]);
    }

    /** the monitor's rules (admin) */
    public function saveRules(Request $request)
    {
        $this->only($request->user(), ['admin']);
        $data = $request->validate([
            'calling_shift_start' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'calling_grace_min' => 'required|integer|min:0|max:240', 'calling_idle_min' => 'required|integer|min:5|max:480',
            'calling_break_min' => 'required|integer|min:5|max:480', 'calling_overdue_max' => 'required|integer|min:0|max:1000',
            'calling_daily_target' => 'required|integer|min:1|max:1000',
        ], ['calling_shift_start.regex' => 'Enter the shift start as HH:MM, e.g. 10:00.']);
        DB::table('settings')->exists() ? DB::table('settings')->update($data) : DB::table('settings')->insert($data);
        return back()->with('success', 'Monitor rules saved.');
    }

    // ---- the two dashboards ----

    private function teamLead(Admin $u)
    {
        $ids = $this->cc->scope($u);
        $team = array_values(array_diff($ids, [$u->id]));
        $today = $this->cc->metrics($ids, ...$this->cc->range('today'));
        $queues = $this->cc->queues($ids);
        return view('admin.calling.dash-teamlead', $this->shared($u) + $this->clock($u) + [
            'ids' => $ids, 'progress' => $this->cc->progress($ids), 'today' => $today, 'teamToday' => $this->cc->sum($today),
            'queues' => $queues, 'live' => $this->cc->live($ids), 'pending' => $this->cc->pendingLeaves($team),
            'daily' => $this->cc->dailyTarget($u->id), 'overdueTeam' => array_sum(array_column($queues, 'overdue')),
            'att' => $this->cc->monthAttendance($u->id),
            'escalations' => app(LeadFlags::class)->openFor($u), 'important' => app(LeadFlags::class)->importantFor($ids, 15),
        ]);
    }

    private function telecaller(Admin $u)
    {
        $id = $u->id;
        $month = $this->cc->metrics([$id], ...$this->cc->range('month'))[$id];
        // the team's ranking this month: admissions, then connected calls
        $peers = array_values(array_unique(array_merge([$id], $u->manager_id ? array_intersect($this->cc->below((int) $u->manager_id), $this->cc->callers()->keys()->all()) : [])));
        $peers = array_values(array_filter($peers, fn ($p) => $this->cc->role($this->cc->callers()[$p] ?? null) === 'telecaller'));
        $pm = $this->cc->metrics($peers, ...$this->cc->range('month'));
        uasort($pm, fn ($a, $b) => [$b['adm'], $b['conn']] <=> [$a['adm'], $a['conn']]);
        return view('admin.calling.dash-telecaller', $this->shared($u) + $this->clock($u) + [
            'progress' => $this->cc->progress([$id], $id), 'today' => $this->cc->metrics([$id], ...$this->cc->range('today'))[$id],
            'month' => $month, 'queue' => $this->cc->queues([$id])[$id], 'daily' => $this->cc->dailyTarget($id),
            'rank' => array_search($id, array_keys($pm)) + 1, 'peers' => count($pm), 'att' => $this->cc->monthAttendance($id),
            'live' => $this->cc->live([$id])[$id], 'leave' => $this->cc->leaveBalance($id), 'holidays' => $this->cc->upcomingHolidays($id),
            'disposition' => $this->cc->disposition([$id], ...$this->cc->range('month')),
            'important' => app(LeadFlags::class)->importantFor([$id], 15), 'escalated' => app(LeadFlags::class)->raisedBy($id),
        ]);
    }

    // ---- internals ----

    private function only(?Admin $u, array $roles): void
    {
        abort_unless(in_array($this->cc->role($u), $roles, true), 403, 'This page is for the calling team.');
    }

    /** what every command-center page needs: the role, the notices, who the person is */
    private function shared(Admin $u): array
    {
        return ['cc' => $this->cc, 'me' => $u, 'role' => $this->cc->role($u), 'notices' => $this->notices,
            'board' => $this->notices->visible($u, false, 4), 'unacked' => $this->notices->unacked($u)];
    }

    /** today's open attendance and break, for the punch buttons */
    private function clock(Admin $u): array
    {
        $attendance = \App\Models\Attendance::where('staff_id', $u->id)->whereDate('date', Carbon::today())->whereNull('check_out')->first();
        return ['attendance' => $attendance, 'breaks' => $attendance ? \App\Models\Breaks::where('attendance_id', $attendance->id)->whereNull('end')->first() : null,
            'punchedOut' => !$attendance && \App\Models\Attendance::where('staff_id', $u->id)->whereDate('date', Carbon::today())->exists()];
    }
}
