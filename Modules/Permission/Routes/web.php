<?php

use Illuminate\Support\Facades\Route;

Route::group([
    "prefix" => "admin",
    "as" => "admin.",
    // "middleware" => ["auth"]
], function () {
    Route::group(
        [
            "prefix" => "roles",
            "as" => "roles."
        ],
        function () {
            Route::get("manage/{id}", [\Modules\Permission\Http\Controllers\RoleController::class, "showManageForm"])->name('manage');
            Route::post("manage/{id}", [\Modules\Permission\Http\Controllers\RoleController::class, "manage"])->name('manage.update');
        }
    );
    Route::get('roles/data',  [\Modules\Permission\Http\Controllers\RoleController::class, 'dataTable']);
    Route::resource("roles", \Modules\Permission\Http\Controllers\RoleController::class, ['except' => 'show']);
    Route::get('permissions/data', [\Modules\Permission\Http\Controllers\PermissionsController::class, 'dataTable']);
    Route::resource("permissions", \Modules\Permission\Http\Controllers\PermissionsController::class, ['except' => 'show']);
});
