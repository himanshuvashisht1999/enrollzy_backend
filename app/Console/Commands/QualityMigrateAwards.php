<?php

namespace App\Console\Commands;

use App\Models\Quality\EntityAward;
use App\Services\Quality\LegacyAwards;
use Illuminate\Console\Command;

/** Command-line twin of the awards step on /admin/quality/setup. Dry run unless --apply. Safe to re-run. */
class QualityMigrateAwards extends Command
{
    protected $signature = 'quality:migrate-awards {--apply : Write the records. Without this flag nothing is changed.}';
    protected $description = 'Turn the old awards_and_recognition / awards_or_recognition text lists into unverified award records';

    public function handle(LegacyAwards $migrator): int
    {
        $apply = (bool) $this->option('apply');
        $r = $migrator->run($apply);
        foreach ($r['counts'] as $k => $v) $this->line(sprintf('  %-28s %s', $k, number_format($v)));
        $this->line('  By type:');
        foreach ($r['by_kind'] as $k => $v) $this->line(sprintf('    %-26s %s', EntityAward::SHORT_KINDS[$k], number_format($v)));
        $this->info($apply ? 'Done. Sort the entries in Admin > Rankings & Accreditation > Review queue > Awards.' : 'Dry run only. Re-run with --apply to write them.');
        return self::SUCCESS;
    }
}
