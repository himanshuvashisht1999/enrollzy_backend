<?php

namespace App\Models;


use App\Models\Wallet;
use App\Models\UserPurchase;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Users extends Model
{
    use SoftDeletes;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public static function createOrUpdateUser($data)
    {
        $user = self::updateOrCreate(
            ['phone' => $data['phone']],
            $data
        );
        if ($user->wasRecentlyCreated) {
            staffLog('users', $user->id, 'create', ' user created');
            CreateWallet($user->id);
            staffLog('users', $user->id, 'create', ' user wallet created');
        }
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet) {
            CreateWallet($user->id);
            staffLog('users', $user->id, 'create', ' user wallet created');
        }
        return $user->id;
    }

    public function sellItem()
    {
        return $this->belongsTo(UserPurchase::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }


    public function orders()
    {
        return $this->hasMany(MasterOrder::class, 'user_id');
    }
    public function category()
    {
        return $this->belongsTo(CustomerCategories::class, 'category_id');
    }
}
