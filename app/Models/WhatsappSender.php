<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsappSender extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'whatsapp_senders';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function messages()
    {
        return $this->hasMany(WhatsappMessage::class, 'whatsapp_sender_id');
    }
}
