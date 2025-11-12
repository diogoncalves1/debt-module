<?php

namespace Modules\Permission\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;

    protected $fillable = ["name", "code"];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permissions::class, 'permission_role', null, 'permission_id');
    }

    public function proles()
    {
        return $this->hasMany(PermissionRole::class);
    }
}