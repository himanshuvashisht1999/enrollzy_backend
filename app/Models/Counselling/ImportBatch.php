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
}
