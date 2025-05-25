<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'role',
    'middleware' => ['IjpAuth']
], function () {
    Route::controller(RoleController::class)->group(function () {
        // Route::get('/role', 'index')->name('auth.role.index');
        Route::get('/', 'show')->name('auth.role.show');
        Route::get('/{id}', 'showbyid')->name('auth.role.show.id');
        Route::post('/', 'store')->name('auth.role.store');
        Route::patch('/{id}', 'update')->name('auth.role.update');
        Route::delete('/{id}', 'delete')->name('auth.role.destroy');
        Route::post('/permission', 'addPermissionToRole')->name('auth.role.add.permission');
        Route::put('/permissions', 'updateRolePermission')->name('auth.role.update.permission.id');
    });
});
