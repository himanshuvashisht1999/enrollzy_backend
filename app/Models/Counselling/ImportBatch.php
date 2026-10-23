<?php

namespace App\Models\Counselling;

use App\Models\ExamModule\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class ImportBatch extends Model
{
    use Auditable;

    protected $table = 'cns_import_batches';
    protected $fillable = ['type', 'cycle_id', 'round_id', 'mode', 'make_live', 'label', 'published_on', 'source_url', 'file_name', 'file_path',
        'status', 'total_rows', 'matched_rows', 'warning_rows', 'error_rows', 'saved_rows', 'created_by', 'confirmed_at', 'undone_at'];
    protected $casts = ['make_live' => 'boolean', 'published_on' => 'date', 'confirmed_at' => 'datetime', 'undone_at' => 'datetime'];

    public const TYPES = ['participants' => 'Participating institutes', 'seats' => 'Seat matrix', 'cutoffs' => 'Cutoffs'];

    public function cycle() { return $this->belongsTo(CounsellingCycle::class, 'cycle_id'); }
    public function round() { return $this->belongsTo(Round::class, 'round_id'); }
    public function rows() { return $this->hasMany(ImportRow::class, 'batch_id'); }
    public function user() { return $this->belongsTo(\App\Models\Admin::class, 'created_by'); }

    /** What undoing this (confirmed) import will do, in plain sentences for the confirmation dialog. */
    public function undoSummary(): array
    {
        $n = number_format((int) $this->saved_rows);
        if ($this->type === 'seats') {
            $version = SeatMatrixVersion::where('import_batch_id', $this->id)->first();
            return array_values(array_filter([
                $version ? "Seat matrix version {$version->version_no}" . ($version->label ? " (\"{$version->label}\")" : '') . " made by this import is removed, with its " . number_format((int) $version->row_count) . ' rows.'
                    : 'The seat matrix version made by this import is removed.',
                $version?->is_live ? 'It is the live version now: the latest remaining version becomes live instead.' : null,
                'Other versions are not changed.',
            ]));
        }
        $what = $this->type === 'participants' ? 'participating institute' : 'cutoff';
        return array_values(array_filter([
            "The $n $what row(s) saved by this import are removed.",
            $this->mode === 'replace' ? 'The rows this import replaced are put back.' : null,
            'Rows added by hand or by other imports are not changed.',
        ]));
    }
}
