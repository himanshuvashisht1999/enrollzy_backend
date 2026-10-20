<?php

namespace App\Console\Commands;

use App\Services\Quality\LegacyMigrator;
use Illuminate\Console\Command;

/** Command-line twin of the "Run update" button on /admin/quality/setup (legacy values step only). */
class QualityMigrateLegacy extends Command
{
    protected $signature = 'quality:migrate-legacy {--apply : Write the records. Without this flag nothing is changed.}';
    protected $description = 'Migrate legacy NAAC / UGC / AICTE / NIRF columns into entity_accreditations and entity_rankings';

    public function handle(LegacyMigrator $migrator): int
    {
        $apply = (bool) $this->option('apply');
        $r = $migrator->run($apply);
        if (!$r['ok']) { $this->error($r['error']); return self::FAILURE; }

        foreach ($r['warnings'] as $w) $this->warn('  ' . $w);
        foreach ($r['counts'] as $k => $v) $this->line(sprintf('  %-40s %d', $k, $v));
        $this->line("  {$r['acc_rows']} accreditation rows and {$r['rank_rows']} ranking rows " . ($apply ? 'written.' : 'would be written.'));
        $this->info($apply ? 'Done. All migrated rows are UNVERIFIED; review them in Admin > Rankings & Accreditation.' : 'Dry run only. Re-run with --apply to write them.');
        return self::SUCCESS;
    }
}
