<?php

use App\Http\Controllers\Permissions\PermissionController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'permission', 'middleware' => ['IjpAuth']], function () {
    Route::controller(PermissionController::class)->group(function () {
        Route::post('/', 'store')->name('permission.store');
        Route::patch('/{id}', 'update')->name('permission.update');
        Route::delete('/{id}', 'delete')->name('permission.delete');
        Route::get('/', 'showAllPermissions')->name('permission.show');
        Route::get('/{id}', 'showPermissionByID')->name('permission.showById');
        Route::get('/roles/{id}', 'showPermissionByRoleID')->name('permission.showByRoleID');
    });
});
