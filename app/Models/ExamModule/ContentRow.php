<?php

namespace App\Models\ExamModule;

use App\Models\ExamModule\Concerns\Auditable;
use App\Support\ExamModule\ContentBlocks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One generic model for all cycle content tables (see ContentBlocks).
 * Obtain a configured instance with ContentRow::block('fees').
 */
class ContentRow extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    public string $blockKey = '';

    public static function block(string $key): static
    {
        $def = ContentBlocks::get($key) ?? abort(404);
        $m = new static;
        $m->blockKey = $key;
        $m->setTable($def['table']);

        $casts = [];
        foreach ($def['fields'] as $f) {
            $casts[$f['name']] = match ($f['type']) {
                'list', 'rows' => 'array',
                'bool' => 'boolean',
                'date' => 'date:Y-m-d',
                default => null,
            };
        }
        $m->mergeCasts(array_filter($casts));
        return $m;
    }

    /**
     * One class serves many tables, but Eloquent caches the guardable column list per class,
     * which would hide every other block's columns. Input is already validated against ContentBlocks.
     */
    public function isGuarded($key)
    {
        return $key === 'id';
    }

    /** replicate() builds a bare `new static`, which would lose the block's table and casts. */
    public function replicate(?array $except = null)
    {
        $copy = parent::replicate($except);
        $copy->blockKey = $this->blockKey;
        $copy->setTable($this->getTable());
        $copy->mergeCasts($this->getCasts());
        return $copy;
    }

    public function newInstance($attributes = [], $exists = false)
    {
        $m = parent::newInstance($attributes, $exists);
        $m->blockKey = $this->blockKey;
        return $m;
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /** Audit entries name the block (e.g. "Fees #3") because every block shares this class. */
    protected function writeAudit(string $action, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'auditable_type' => 'App\\Models\\ExamModule\\Block\\' . Str::studly($this->blockKey ?: $this->getTable()),
            'auditable_id' => $this->getKey(),
            'user_id' => auth('admin')->id(),
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }
}
