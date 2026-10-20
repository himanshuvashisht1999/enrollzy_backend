<?php

namespace App\Console\Commands;

use App\Services\Quality\LegacyFreeText;
use Illuminate\Console\Command;

/** Command-line twin of the free-text step on /admin/quality/setup. Dry run unless --apply. Safe to re-run. */
class QualityMigrateFreeText extends Command
{
    protected $signature = 'quality:migrate-freetext {--apply : Write the records. Without this flag nothing is changed.}';
    protected $description = 'Turn the old statutory_approvals / international_accreditations text lists into unverified approval and accreditation records';

    public function handle(LegacyFreeText $migrator): int
    {
        $apply = (bool) $this->option('apply');
        $r = $migrator->run($apply);
        foreach ($r['counts'] as $k => $v) $this->line(sprintf('  %-26s %s', $k, number_format($v)));
        $this->line('  Records by body:');
        foreach ($r['by_body'] as $k => $v) $this->line(sprintf('    %-24s %s', $k, number_format($v)));
        $this->line('  Parts left alone, and why:');
        foreach ($r['skipped'] as $k => $v) $this->line(sprintf('    %-5s %s', number_format($v), $k));
        if ($r['no_master']) {
            $this->line('  Bodies named in the text that have no master:');
            foreach ($r['no_master'] as $k => $v) $this->line(sprintf('    %-5s %s', number_format($v), $k));
        }
        $this->info($apply ? 'Done. Check them in Admin > Rankings & Accreditation > Review queue.' : 'Dry run only. Re-run with --apply to write them.');
        return self::SUCCESS;
    }
}
