<?php

namespace App\Models\Counselling;

use Illuminate\Database\Eloquent\Model;

/** A name the importer has been taught to resolve ("SRM Univ." -> a specific organisation). */
class Alias extends Model
{
    protected $table = 'cns_aliases';
    protected $fillable = ['type', 'alias', 'alias_norm', 'target_id', 'created_by'];

    /** Lower-case, punctuation-free, single-spaced: the form names are compared in. */
    public static function normalize(?string $s): string
    {
        $s = mb_strtolower(trim((string) $s));
        $s = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $s);
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    public static function learn(string $type, string $raw, int $targetId): void
    {
        $norm = static::normalize($raw);
        if ($norm === '') {
            return;
        }
        static::updateOrCreate(
            ['type' => $type, 'alias_norm' => $norm],
            ['alias' => trim($raw), 'target_id' => $targetId, 'created_by' => auth('admin')->id()]
        );
    }
}
