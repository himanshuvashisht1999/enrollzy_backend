<?php

namespace App\Models\Counselling;

use Illuminate\Database\Eloquent\Model;

class AlertDelivery extends Model
{
    protected $table = 'cns_alert_deliveries';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['sent_at' => 'datetime', 'read_at' => 'datetime', 'created_at' => 'datetime'];

    public function alert() { return $this->belongsTo(Alert::class, 'alert_id'); }
    public function subscriber() { return $this->belongsTo(Subscriber::class, 'subscriber_id'); }
}
