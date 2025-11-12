<?php

use Illuminate\Support\Facades\Route;

Route::group([
    "as" => "admin.",
    "prefix" => "admin/",
    // "middleware" => "auth"
], function () {
    Route::resource('currencies', \Modules\Currency\Http\Controllers\CurrencyController::class, ['except' => ['store', 'update', 'destroy', /*'show'*/]]);
});

Route::group([
    'as' => "api.",
    "prefix" => "api/",
    // "middleware" => "auth"
], function () {
    Route::group([
        'prefix' => 'currencies/'
    ], function () {
        Route::get("check-code", [\Modules\Currency\Http\Controllers\Api\CurrencyController::class, "checkCode"]);
        Route::get("update-rates", [\Modules\Currency\Http\Controllers\Api\CurrencyController::class, "updateRates"]);
        Route::get('data', [\Modules\Currency\Http\Controllers\Api\CurrencyController::class, 'dataTable']);
    });
    Route::resource('currencies', \Modules\Currency\Http\Controllers\Api\CurrencyController::class, ['except' => ['index', 'create', 'edit']]);
});
