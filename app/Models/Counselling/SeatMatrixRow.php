<?php

namespace App\Models\Counselling;

use Illuminate\Database\Eloquent\Model;

/** Seat rows are bulk-imported by the thousand, so they are not audited one by one (the import batch is). */
class SeatMatrixRow extends Model
{
    protected $table = 'cns_seat_matrix_rows';
    public $timestamps = false;
    protected $fillable = ['version_id', 'organisation_id', 'course_id', 'quota_id', 'category_id', 'gender', 'pwd', 'seats'];
    protected $casts = ['pwd' => 'boolean'];

    public function version() { return $this->belongsTo(SeatMatrixVersion::class, 'version_id'); }
    public function organisation() { return $this->belongsTo(\App\Models\Organisation::class, 'organisation_id'); }
    public function course() { return $this->belongsTo(\App\Models\Course::class, 'course_id'); }
    public function quota() { return $this->belongsTo(Quota::class, 'quota_id'); }
    public function category() { return $this->belongsTo(Category::class, 'category_id'); }
}
