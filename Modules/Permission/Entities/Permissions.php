<?php

namespace Modules\Permission\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permissions extends Model
{
    /** @use HasFactory<\Database\Factories\PermissionsFactory> */
    use HasFactory;

    protected $fillable = ["name", "code", "category"];

    public function roles()
    {
        return  $this->belongsToMany(Role::class);
    }
}
