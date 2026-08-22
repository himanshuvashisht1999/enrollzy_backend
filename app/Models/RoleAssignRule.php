<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class RoleAssignRule extends Model
{
    use HasFactory;

    protected $fillable = ["role_id", "can_assign_to_role_id"];

    public function role()
    {
        return $this->belongsTo(Role::class, "role_id");
    }

    public function assignableRole()
    {
        return $this->belongsTo(Role::class, "can_assign_to_role_id");
    }
}
