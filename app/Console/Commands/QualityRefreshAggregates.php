<?php

namespace App\Console\Commands;

use App\Services\Quality\Aggregates;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Keeps the OLD typed columns that the website may still read (organisations.average_rating / total_reviews, organisation_courses.rating)
 * equal to the computed value, but ONLY where verified reviews exist. Where there are none, the old typed value is left untouched
 * (nothing is blanked), so running this can never make a page lose a number it has today.
 * Dry run unless --apply. Schedule it (e.g. nightly) once real reviews are being verified.
 */
class QualityRefreshAggregates extends Command
{
    protected $signature = 'quality:refresh-aggregates {--apply : Write the computed values. Without this flag nothing is changed.}';
    protected $description = 'Write computed review averages into the legacy rating columns (only where verified reviews exist)';

    public function handle(Aggregates $agg): int
    {
        $apply = (bool) $this->option('apply');
        $changed = 0; $same = 0;

        // Only entities that actually have a verified, published review at or below them can be computed.
        $orgIds = DB::table('organisations')->pluck('id');
        foreach ($orgIds->chunk(200) as $chunk) {
            foreach ($chunk as $id) {
                $r = $agg->ratings('organisation', $id, true);
                if ($r['count'] === 0 || $r['average'] === null) continue;
                $old = DB::table('organisations')->where('id', $id)->first(['name', 'average_rating', 'total_reviews']);
                if ((float) $old->average_rating === (float) $r['average'] && (int) $old->total_reviews === $r['count']) { $same++; continue; }
                $this->line(sprintf('  org #%d %s: %s (%s reviews) -> %s (%d reviews)', $id, $old->name, $old->average_rating ?? 'none', $old->total_reviews ?? 0, $r['average'], $r['count']));
                if ($apply) DB::table('organisations')->where('id', $id)->update(['average_rating' => $r['average'], 'total_reviews' => $r['count']]);
                $changed++;
            }
        }

        $courseIds = DB::table('entity_reviews')->whereNull('deleted_at')->where('entity_type', 'course')->where('is_published', 1)->where('verification_status', 'verified')->distinct()->pluck('entity_id');
        foreach ($courseIds as $id) {
            $r = $agg->ratings('course', $id, true);
            if ($r['count'] === 0 || $r['average'] === null) continue;
            $old = DB::table('organisation_courses')->where('id', $id)->value('rating');
            $new = round($r['average'], 1);
            if ($old !== null && (float) $old === $new) { $same++; continue; }
            $this->line(sprintf('  programme #%d: %s -> %s (%d reviews)', $id, $old ?? 'none', $new, $r['count']));
            if ($apply) DB::table('organisation_courses')->where('id', $id)->update(['rating' => $new]);
            $changed++;
        }

        $this->info(($apply ? 'Updated ' : 'Would update ') . "$changed value(s); $same already equal the computed value. Entities without verified reviews were left untouched.");
        if (!$apply && $changed) $this->line('Re-run with --apply to write them.');
        return self::SUCCESS;
    }
}
