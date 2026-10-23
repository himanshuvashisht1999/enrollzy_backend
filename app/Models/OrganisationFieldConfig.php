<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganisationFieldConfig extends Model
{
    use HasFactory;

    protected $table = 'organisation_field_configs';

    protected $fillable = [
        'organisation_type_id',
        'fields_config',
    ];

    protected $casts = [
        'fields_config' => 'array',
    ];

    /**
     * Relationship to OrganisationType
     */
    public function organisationType(): BelongsTo
    {
        return $this->belongsTo(OrganisationType::class, 'organisation_type_id');
    }

    /**
     * Check if a specific field is enabled for a given organisation type and entity.
     * If no configuration has been saved yet, defaults to true (enabled).
     */
    public static function isFieldEnabled(int $organisationTypeId, string $entityType, string $fieldName): bool
    {
        $config = static::where('organisation_type_id', $organisationTypeId)->first();
        if (!$config || empty($config->fields_config)) {
            return true;
        }

        $entityFields = $config->fields_config[$entityType] ?? null;
        if ($entityFields === null) {
            return true;
        }

        return in_array($fieldName, (array) $entityFields, true);
    }

    /**
     * Retrieve enabled fields for an organisation type.
     */
    public static function getEnabledFields(int $organisationTypeId, ?string $entityType = null): array
    {
        $config = static::where('organisation_type_id', $organisationTypeId)->first();
        if (!$config || empty($config->fields_config)) {
            return [];
        }

        if ($entityType) {
            return $config->fields_config[$entityType] ?? [];
        }

        return $config->fields_config;
    }

    /**
     * Save or update field configuration for an organisation type.
     */
    public static function saveTypeConfig(int $organisationTypeId, array $fieldsConfig): self
    {
        return static::updateOrCreate(
            ['organisation_type_id' => $organisationTypeId],
            ['fields_config' => $fieldsConfig]
        );
    }
}