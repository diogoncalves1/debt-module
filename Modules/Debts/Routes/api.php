<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Debts\Http\Controllers\Api\V1\DebtController;
use Modules\Debts\Http\Controllers\Api\V1\DebtPaymentController;

Route::group([
    'prefix' => 'v1'
], function () {
    Route::group([
        'middleware' => ["auth:sanctum", "setlocale"]
    ], function () {
        Route::post('debts/{id}/mark-paid', [DebtController::class, 'markPaid']);
        Route::resource('debts', DebtController::class, ["except" => ["edit", "create"]]);
        Route::post('debts-payments/{id}/confirm', [DebtPaymentController::class, 'confirm']);
        Route::resource('debt-payments', DebtPaymentController::class, ["except" => ["edit", "create"]]);
    });
});
