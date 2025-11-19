<?php

namespace App\Http\Controllers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Gate;

class AppController
{
    protected function allowedAction($permission)
    {
        if (Gate::denies('authorization', $permission)) {
            throw new AuthenticationException('This action is unauthorized');
        }
    }
}
