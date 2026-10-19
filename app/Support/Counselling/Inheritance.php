<?php

namespace App\Support\Counselling;

use App\Models\Counselling\CnsRow;
use App\Models\Counselling\Round;
use Illuminate\Support\Collection;

/**
 * What a round actually applies: the cycle's rows, with the round's overrides and additions
 * (spec section 9).
 *
 *   effective(round) = cycle rows
 *                      REPLACED BY round rows with the same key   (blocks with inherit = 'replace')
 *                      PLUS round rows with no cycle equivalent
 *
 * Blocks with inherit = 'add' (process steps, documents) simply list the round's rows after the cycle's.
 */
class Inheritance
{
    /**
     * @return Collection<int, array{row: CnsRow, source: string, replaces: ?CnsRow}>
     *         source: 'inherited' (from cycle), 'overridden' (round row replacing a cycle row), 'round' (round only)
     */
    public static function effective(string $block, Round $round): Collection
    {
        $def = CnsBlocks::get($block) ?? abort(404);
        $cycleRows = CnsRow::forScope($block, $round->cycle_id, null);
        $roundRows = CnsRow::forScope($block, $round->cycle_id, $round->id);

        if (($def['inherit'] ?? 'add') === 'add') {
            return $cycleRows->map(fn ($r) => ['row' => $r, 'source' => 'inherited', 'replaces' => null])
                ->concat($roundRows->map(fn ($r) => ['row' => $r, 'source' => 'round', 'replaces' => null]))->values();
        }

        $roundByKey = $roundRows->keyBy(fn ($r) => self::key($def['unique'], $r));
        $cycleKeys = $cycleRows->map(fn ($r) => self::key($def['unique'], $r))->all();

        $out = collect();
        foreach ($cycleRows as $cycleRow) {
            $override = $roundByKey->get(self::key($def['unique'], $cycleRow));
            $out->push($override
                ? ['row' => $override, 'source' => 'overridden', 'replaces' => $cycleRow]
                : ['row' => $cycleRow, 'source' => 'inherited', 'replaces' => null]);
        }
        foreach ($roundRows as $r) {
            if (!in_array(self::key($def['unique'], $r), $cycleKeys, true)) {
                $out->push(['row' => $r, 'source' => 'round', 'replaces' => null]);
            }
        }
        return $out->values();
    }

    /** Null-safe identity of a row within its scope: a blank quota and a missing quota are the same thing. */
    public static function key(array $fields, $row): string
    {
        return implode('|', array_map(fn ($f) => (string) ($row->$f ?? '*'), $fields));
    }
}
