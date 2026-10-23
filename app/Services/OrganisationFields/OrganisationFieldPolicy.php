<?php

namespace App\Services\OrganisationFields;

use App\Models\OrganisationFieldConfig;
use App\Services\OrganisationFieldCatalogService;
use Illuminate\Support\Facades\Schema;

/**
 * The single answer to "may the AI updater read or write this field?". A field is usable only when ALL of these hold:
 *   catalogue -> real database column -> on the edit form staff actually use for that organisation type -> ticked on the
 *   Organisation Fields screen -> not "managed elsewhere" (optional switch in config/organisation_fields.php).
 * Everything that offers, shows, saves or applies AI changes asks this class, so the screen, the form and the AI can no longer drift apart.
 */
class OrganisationFieldPolicy
{
    public const LEVELS = ['organisation', 'campus', 'department', 'course'];
    public const TABLES = ['organisation' => 'organisations', 'campus' => 'campuses', 'department' => 'departments', 'course' => 'organisation_courses'];
    /** Plural names used by the AI updater's request / response shape. */
    public const PLURAL = ['organisation' => 'organisation', 'campus' => 'campuses', 'department' => 'departments', 'course' => 'courses'];

    public const REASONS = [
        'ok' => 'Available',
        'no_column' => 'No database column: it can never be saved',
        'not_on_form' => 'Not on the edit form: staff could not see or correct it',
        'form_unknown' => 'The edit form could not be read',
        'managed_elsewhere' => 'Managed as records (Rankings & Accreditation)',
    ];

    private array $columnCache = [];
    private array $stateCache = [];

    public function __construct(private OrganisationFieldCatalogService $catalog, private FormFieldScanner $scanner) {}

    /** Accepts organisation|campus|department|course and their plural forms. */
    public static function level(string $level): string
    {
        return ['campuses' => 'campus', 'departments' => 'department', 'courses' => 'course'][$level] ?? $level;
    }

    /** @return array<string,string> name => label, for this level (and organisation type). */
    public function catalogue(string $level, int $typeId): array
    {
        return $this->catalog->getFieldLabels(self::level($level), $typeId);
    }

    /**
     * State of every catalogue field: ok | no_column | not_on_form | form_unknown | managed_elsewhere.
     *
     * @return array<string, array{label:string, state:string, reason:string, on_form:?bool}>
     */
    public function states(string $level, int $typeId): array
    {
        $level = self::level($level);
        if (isset($this->stateCache[$level][$typeId])) return $this->stateCache[$level][$typeId];

        $form = $this->scanner->formFor($level, $typeId);
        $formFields = $form ? array_flip($form['fields']) : null;
        $aliases = config("organisation_fields.form_aliases.$level", []);
        $managed = config('organisation_fields.block_quality_managed') ? array_flip(config("organisation_fields.quality_managed.$level", [])) : [];

        $out = [];
        foreach ($this->catalogue($level, $typeId) as $name => $label) {
            if (!$this->hasColumn($level, $name)) { $state = 'no_column'; $onForm = null; }
            elseif ($formFields === null) { $state = 'form_unknown'; $onForm = null; }
            else {
                $onForm = isset($formFields[$name]) || isset($formFields[$aliases[$name] ?? '']);
                $state = !$onForm ? 'not_on_form' : (isset($managed[$name]) ? 'managed_elsewhere' : 'ok');
            }
            $out[$name] = ['label' => $label, 'state' => $state, 'reason' => self::REASONS[$state], 'on_form' => $onForm];
        }
        return $this->stateCache[$level][$typeId] = $out;
    }

    /** Names ticked on the Organisation Fields screen for this type. Null config (never saved) means "everything". Accepts old plural keys. */
    public function ticked(string $level, int $typeId): ?array
    {
        $level = self::level($level);
        $cfg = OrganisationFieldConfig::where('organisation_type_id', $typeId)->first()?->fields_config;
        if (empty($cfg)) return null;
        $renames = config("organisation_fields.catalogue_renames.$level", []);
        foreach ([$level, self::PLURAL[$level]] as $key) {
            if (array_key_exists($key, $cfg)) return array_values(array_unique(array_map(fn ($n) => $renames[$n] ?? $n, (array) $cfg[$key])));
        }
        return null;
    }

    /** What the AI may touch at this level for this organisation type. */
    public function allowed(string $level, int $typeId): array
    {
        $ticked = $this->ticked($level, $typeId);
        $names = [];
        foreach ($this->states($level, $typeId) as $name => $s) {
            if ($s['state'] === 'ok' && ($ticked === null || in_array($name, $ticked, true))) $names[] = $name;
        }
        return $names;
    }

    /** The shape the AI updater uses everywhere: plural keys for the sub-levels. */
    public function forAi(int $typeId): array
    {
        $out = [];
        foreach (self::LEVELS as $level) $out[self::PLURAL[$level]] = $this->allowed($level, $typeId);
        return $out;
    }

    /**
     * Keys in $keys the AI may NOT write at this level, with the reason. Empty array = all fine.
     *
     * @return array<string,string>
     */
    public function blocked(string $level, int $typeId, array $keys): array
    {
        $level = self::level($level);
        $states = $this->states($level, $typeId);
        $ticked = $this->ticked($level, $typeId);
        $blocked = [];
        foreach ($keys as $key) {
            if (!isset($states[$key])) { $blocked[$key] = 'not in the field catalogue for this organisation type'; continue; }
            if ($states[$key]['state'] !== 'ok') { $blocked[$key] = $states[$key]['reason']; continue; }
            if ($ticked !== null && !in_array($key, $ticked, true)) $blocked[$key] = 'not ticked on the Organisation Fields screen for this type';
        }
        return $blocked;
    }

    /** Of the given names, only those that can really be used at this level for this type (in the catalogue, a column, on the form). Order kept. */
    public function keepUsable(string $level, int $typeId, array $names): array
    {
        $states = $this->states($level, $typeId);
        return array_values(array_filter($names, fn ($n) => ($states[$n]['state'] ?? 'absent') === 'ok'));
    }

    /** Fields to drop when saving the screen, because they could never be used. */
    public function unusable(string $level, int $typeId): array
    {
        return array_keys(array_filter($this->states($level, $typeId), fn ($s) => $s['state'] !== 'ok'));
    }

    /** Counts per state for a level and type (for the audit). */
    public function summary(string $level, int $typeId): array
    {
        $counts = array_fill_keys(array_keys(self::REASONS), 0);
        foreach ($this->states($level, $typeId) as $s) $counts[$s['state']]++;
        $counts['total'] = array_sum($counts);
        return $counts;
    }

    public function scanner(): FormFieldScanner
    {
        return $this->scanner;
    }

    private function hasColumn(string $level, string $name): bool
    {
        $table = self::TABLES[$level];
        if (!isset($this->columnCache[$table])) $this->columnCache[$table] = array_flip(Schema::getColumnListing($table));
        return isset($this->columnCache[$table][$name]);
    }
}
