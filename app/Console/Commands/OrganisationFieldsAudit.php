<?php

namespace App\Console\Commands;

use App\Models\OrganisationType;
use App\Services\OrganisationFields\OrganisationFieldPolicy;
use Illuminate\Console\Command;

/** Command-line twin of Organisation Fields > audit. With --strict it exits 1 when anything cannot be used, so it can run in a deploy check. */
class OrganisationFieldsAudit extends Command
{
    protected $signature = 'organisation-fields:audit {--rescan : Re-read the edit forms first} {--strict : Exit with an error if any catalogue field cannot be used}';
    protected $description = 'Compare the organisation field catalogue with the database and the real edit forms';

    public function handle(OrganisationFieldPolicy $policy): int
    {
        $m = $policy->scanner()->manifest((bool) $this->option('rescan'));
        $this->line('Forms read: ' . ($m['generated_at'] ?? 'never'));
        foreach ($m['errors'] ?? [] as $e) $this->error('  could not read: ' . $e);

        $rows = []; $bad = 0;
        foreach (OrganisationType::orderBy('id')->pluck('title', 'id') as $typeId => $title) {
            foreach (OrganisationFieldPolicy::LEVELS as $level) {
                $s = $policy->summary($level, (int) $typeId);
                $rows[] = [$typeId . ' ' . $title, $level, $s['total'], $s['ok'], $s['no_column'], $s['not_on_form'], $s['form_unknown'], $s['managed_elsewhere']];
                $bad += $s['no_column'] + $s['not_on_form'] + $s['form_unknown'];
            }
        }
        $this->table(['Type', 'Level', 'Catalogue', 'Usable', 'No column', 'Not on form', 'Form unknown', 'Managed elsewhere'], $rows);
        $this->line($bad ? "$bad catalogue field(s) cannot be used by the AI updater." : 'Every catalogue field can be used.');
        $hidden = config('organisation_fields.unavailable', []);
        $byReason = [];
        foreach ($hidden as $level => $fields) foreach ($fields as $name => $reason) $byReason[$reason][] = "$level.$name";
        if ($byReason) {
            $this->line('Hidden from the catalogue because they cannot be saved anywhere (config organisation_fields.unavailable):');
            foreach ($byReason as $reason => $names) $this->line(sprintf('  %-10s %3d  %s', $reason, count($names), implode(', ', $names)));
        }
        return $this->option('strict') && $bad ? self::FAILURE : self::SUCCESS;
    }
}
