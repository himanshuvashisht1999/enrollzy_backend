<?php

namespace App\Models\Counselling;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $table = 'cns_alerts';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['payload' => 'array', 'created_at' => 'datetime'];

    public function deliveries() { return $this->hasMany(AlertDelivery::class, 'alert_id'); }
}
