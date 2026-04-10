<?php

namespace App\Models;

use App\Models\Users;
use App\Models\TicketChat;
use App\Models\MasterOrder;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];

    public function chats()
    {
        return $this->hasMany(TicketChat::class);
    }

    public function user()
    {
        return $this->belongsTo(Users::class);
    }

    public function order()
    {
        return $this->belongsTo(MasterOrder::class);
    }
}