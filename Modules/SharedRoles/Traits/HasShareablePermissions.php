<?php

namespace Modules\SharedRoles\Traits;

trait HasShareablePermissions
{
    public function hasShareableRole($model, $role)
    {
        // return $model->users()
        //     // ->user(auth()->id())
        //     ->join('shared_roles', 'shared_roles.id', '=')
        //     ->where('shared_roles.name', $role)
        //     ->exists();
    }

    public function hasShareablePermission($model, $permission)
    {
        $sharedRole = $model->userSharedRole();

        return $this->hasShareableRole($model, 'creator') ||  $sharedRole->hasPermission($permission);
    }
}
