<?php

use Modules\SharedRoles\Http\Controllers\Api\SharedPermissionController as ApiSharedPermissionController;
use Modules\SharedRoles\Http\Controllers\Api\SharedRoleController as ApiSharedRoleController;
use Modules\SharedRoles\Http\Controllers\SharedPermissionController;
use Modules\SharedRoles\Http\Controllers\SharedPermissionRoleController;
use Modules\SharedRoles\Http\Controllers\SharedRoleController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'as' => 'admin.',
        'prefix' => 'admin/',
        'middleware' => ['auth', 'setlocale']
    ],
    function () {
        Route::group(
            [
                'as' => 'shared-roles.',
                'prefix' => 'shared-roles/'
            ],
            function () {
                Route::get('{id}/manage', [SharedPermissionRoleController::class, 'manage'])->name('manage');

                Route::put('{id}/manage', [SharedPermissionRoleController::class, 'update'])->name('update-permissions');
            }
        );
        Route::resource('shared-roles', SharedRoleController::class, ['except' => ['show', 'destroy']]);
        Route::resource('shared-permissions', SharedPermissionController::class, ['except' => ['show', 'destroy']]);
    }
);

Route::group(
    [
        'as' => 'api.',
        'prefix' => 'api/',
        'middleware' => ['auth', 'setlocale']
    ],
    function () {
        Route::group(
            [
                'as' => 'shared-roles.',
                'prefix' => 'shared-roles/'
            ],
            function () {
                Route::get('data', [ApiSharedRoleController::class, 'dataTable']);
                Route::delete('/{id}', [ApiSharedRoleController::class, 'destroy'])->name('destroy');
                Route::get('check-code', [ApiSharedRoleController::class, 'checkRoleCode']);
            }
        );

        Route::group(
            [
                'as' => 'shared-permissions.',
                'prefix' => 'shared-permissions/'
            ],
            function () {
                Route::get('data', [ApiSharedPermissionController::class, 'dataTable']);
                Route::delete('/{id}', [ApiSharedPermissionController::class, 'destroy'])->name('destroy');
                Route::get('check-code', [ApiSharedPermissionController::class, 'checkPermissionCode']);
            }
        );
    }
);
