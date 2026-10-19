<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SeatMatrixVersion extends Model
{
    use Auditable;

    protected $table = 'cns_seat_matrix_versions';
    protected $fillable = ['round_id', 'version_no', 'label', 'published_on', 'source_url', 'imported_by', 'imported_at', 'is_live', 'row_count', 'import_batch_id'];
    protected $casts = ['is_live' => 'boolean', 'published_on' => 'date', 'imported_at' => 'datetime'];

    public function round() { return $this->belongsTo(Round::class, 'round_id'); }
    public function rows() { return $this->hasMany(SeatMatrixRow::class, 'version_id'); }

    /** Make this the only live version of its round. */
    public function makeLive(): void
    {
        DB::transaction(function () {
            static::where('round_id', $this->round_id)->where('id', '!=', $this->id)->where('is_live', true)->update(['is_live' => false]);
            $this->update(['is_live' => true]);
        });
    }

    public static function nextNumber(int $roundId): int
    {
        return (int) static::where('round_id', $roundId)->max('version_no') + 1;
    }

    /** Seats added / removed / changed against another version, keyed by the seat's identity. */
    public function diffAgainst(self $older): array
    {
        $key = fn ($r) => implode('|', [$r->organisation_id, $r->course_id, $r->quota_id, $r->category_id, $r->gender ?? '*', (int) $r->pwd]);
        $old = $older->rows()->get()->keyBy($key);
        $new = $this->rows()->get()->keyBy($key);

        $added = $new->diffKeys($old)->values();
        $removed = $old->diffKeys($new)->values();
        $changed = $new->filter(fn ($r, $k) => $old->has($k) && $old[$k]->seats !== $r->seats)
            ->map(fn ($r, $k) => ['row' => $r, 'from' => $old[$k]->seats, 'to' => $r->seats])->values();

        return compact('added', 'removed', 'changed');
    }
}
