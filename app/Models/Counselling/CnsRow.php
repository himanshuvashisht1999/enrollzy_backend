<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\ContentRow;
use App\Support\Counselling\CnsBlocks;

/**
 * Generic row for the counselling cycle content tables (eligibility, process, fees, documents, rules).
 * Obtain a configured instance with CnsRow::block('fees').
 */
class CnsRow extends ContentRow
{
    protected static function definitions(): string
    {
        return CnsBlocks::class;
    }

    protected function auditNamespace(): string
    {
        return 'App\\Models\\Counselling\\Block\\';
    }

    public function cycle()
    {
        return $this->belongsTo(CounsellingCycle::class, 'cycle_id');
    }

    public function round()
    {
        return $this->belongsTo(Round::class, 'round_id');
    }

    /** Rows of one block for a cycle (round null = cycle-level rows), in display order. */
    public static function forScope(string $block, int $cycleId, ?int $roundId = null)
    {
        $def = CnsBlocks::get($block);
        $q = static::block($block)->newQuery()->where('cycle_id', $cycleId);
        if (in_array('round', $def['scopes'])) {
            $roundId === null ? $q->whereNull('round_id') : $q->where('round_id', $roundId);
        }

        return $q->orderBy($def['order'] ?? 'id')->orderBy('id')->get();
    }
}
