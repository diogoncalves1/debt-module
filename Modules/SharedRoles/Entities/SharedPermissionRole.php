<?php

namespace Modules\SharedRoles\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedPermissionRole extends Model
{
    /** @use HasFactory<\Database\Factories\SharedPermissionRoleFactory> */
    use HasFactory;

    protected $table = 'shared_permission_roles';
    protected $fillable = ['shared_role_id', 'shared_permission_id'];
    public $timestamps = false;

    public function role()
    {
        return $this->belongsTo(SharedRole::class);
    }

    public function permission()
    {
        return $this->belongsTo(SharedPermission::class);
    }
}
