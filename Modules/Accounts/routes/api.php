<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'v1',
    'as' => 'api.v1.'
], function () {
    Route::group(
        [
            'middleware' => ['auth:sanctum' /*, 'setlocale'*/]
        ],
        function () {
            Route::group([
                'prefix' => 'accounts',
                'as' => 'accounts.'
            ], function () {
                Route::post('/{id}/invite', [\Modules\Accounts\Http\Controllers\Api\V1\AccountUserController::class, 'inviteUser']);
                Route::post('/{id}/accept', [\Modules\Accounts\Http\Controllers\Api\V1\AccountUserController::class, 'acceptInvite']);
                Route::delete('/{id}/invite', [\Modules\Accounts\Http\Controllers\Api\V1\AccountUserController::class, 'destroyInvite']);
                Route::post('/{id}/revoke', [\Modules\Accounts\Http\Controllers\Api\V1\AccountUserController::class, 'revokeInvite']);
                Route::post('/{id}/revoke-user', [\Modules\Accounts\Http\Controllers\Api\V1\AccountUserController::class, 'revokeUser']);
                Route::put('/{id}/user-role', [\Modules\Accounts\Http\Controllers\Api\V1\AccountUserController::class, 'updateUserRole']);
                Route::delete('/{id}/leave', [\Modules\Accounts\Http\Controllers\Api\V1\AccountUserController::class, 'leave']);
            });
            Route::resource('accounts', \Modules\Accounts\Http\Controllers\Api\V1\AccountController::class, ['except' => 'create', 'edit']);

            Route::resource('transactions', \Modules\Accounts\Http\Controllers\Api\V1\TransactionController::class, ['except' => ['show', 'create', 'edit']]);

            Route::post('scheduled-transactions/confirm/{id}', [\Modules\Accounts\Http\Controllers\Api\V1\TransactionController::class, 'confirmSheduled']);
        }
    );
});
