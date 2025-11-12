<?php

namespace Modules\Permission\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
    /** @use HasFactory<\Database\Factories\PermissionRoleFactory> */
    use HasFactory;

    protected $table = 'permission_role';

    protected $fillable = ["role_id", "permission_id"];
    public $timestamps = false;
}