<?php

namespace App\Services\Counselling;

use App\Models\Counselling\CounsellingCycle;
use App\Models\Counselling\RoundType;
use App\Models\ExamModule\AuditLog;
use App\Models\ExamModule\Cycle as ExamCycle;
use Illuminate\Support\Facades\DB;

/**
 * One-time data corrections for the counselling module, run from /admin/counselling/data-fixes (preview, then one click)
 * so live data is corrected without SQL by hand. Each fix is written to the audit log when it runs, and a fix that has
 * run is not offered again, so a value an admin changes deliberately later is never overwritten.
 */
class DataFixes
{
    /** @return array<string, array{title:string, detail:string, pending:int, done:bool}> */
    public function all(): array
    {
        $out = [];
        foreach ($this->fixes() as $key => $fix) {
            $done = $this->done($key);
            $pending = $done ? 0 : ($fix['count'])();
            $out[$key] = ['title' => $fix['title'], 'detail' => $fix['detail'], 'pending' => $pending, 'done' => $done];
        }
        return $out;
    }

    public function pendingCount(): int
    {
        return collect($this->all())->sum('pending');
    }

    /** Runs every fix that has not run yet. @return array<string,int> rows changed per fix */
    public function run(?int $userId, $who = null): array
    {
        $result = [];
        foreach ($this->fixes() as $key => $fix) {
            if ($this->done($key)) {
                continue;
            }
            $result[$key] = DB::transaction(function () use ($key, $fix, $userId, $who) {
                $changed = ($fix['apply'])();
                AuditLog::create(['auditable_type' => 'counselling_data_fix', 'auditable_id' => 0, 'user_id' => $userId, 'action' => 'data_fix',
                    'old_values' => null, 'new_values' => ['fix' => $key, 'changed' => $changed, 'by' => $who ? trim(($who->name ?? '') . ' <' . ($who->email ?? '') . '>') : null]]);
                return $changed;
            });
        }
        return $result;
    }

    private function done(string $key): bool
    {
        return AuditLog::where('auditable_type', 'counselling_data_fix')->where('action', 'data_fix')->where('new_values', 'like', '%"fix":"' . $key . '"%')->exists();
    }

    private function fixes(): array
    {
        return [
            'regular-round-fresh-registration' => [
                'title' => 'Regular round type: fresh registration allowed by default',
                'detail' => 'Regular rounds were set to "No" by default, so every regular round showed "Fresh registration: No (inherited)". Sets the Regular round type default to Allowed. Rounds that override it keep their own value.',
                'count' => fn () => RoundType::where('name', 'Regular')->where('is_fresh_registration_default', false)->count(),
                'apply' => fn () => RoundType::where('name', 'Regular')->where('is_fresh_registration_default', false)->get()
                    ->each(fn ($t) => $t->update(['is_fresh_registration_default' => true]))->count(),
            ],
            'stale-exam-cycle-links' => [
                'title' => 'Copied cycles still linked to last year\'s exam cycle',
                'detail' => 'A cycle copied forward kept the old exam cycle (for example a 2028 counselling on the 2027 exam). Each such link moves to that exam\'s next cycle when one exists; otherwise it is removed and the cycle shows "Needs update".',
                'count' => fn () => count($this->staleLinks()),
                'apply' => function () {
                    $n = 0;
                    foreach ($this->staleLinks() as [$cycleId, $examCycle]) {
                        $cycle = CounsellingCycle::find($cycleId);
                        $next = ExamCycle::where('exam_id', $examCycle->exam_id)->where('year', '>', $examCycle->year)->where('year', '<=', $cycle->year)
                            ->orderByDesc('year')->orderByDesc('is_current')->orderBy('id')->first();
                        $cycle->examCycles()->detach($examCycle->id);
                        if ($next) {
                            $cycle->examCycles()->syncWithoutDetaching([$next->id]);
                        }
                        $n++;
                    }
                    return $n;
                },
            ],
        ];
    }

    /** [counselling cycle id, exam cycle] pairs where the exam cycle's year is older than the counselling cycle's year. */
    private function staleLinks(): array
    {
        return DB::table('cns_cycle_exam_cycles as l')
            ->join('cns_cycles as c', 'c.id', '=', 'l.cycle_id')->whereNull('c.deleted_at')
            ->join('exm_cycles as e', 'e.id', '=', 'l.exam_cycle_id')
            ->whereColumn('e.year', '<', 'c.year')
            ->get(['l.cycle_id', 'e.id', 'e.exam_id', 'e.year'])
            ->map(fn ($r) => [$r->cycle_id, (object) ['id' => $r->id, 'exam_id' => $r->exam_id, 'year' => $r->year]])->all();
    }
}
