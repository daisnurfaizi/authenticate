<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login')->name('auth.login');
        // Route::post('/register', 'register')->name('auth.register');
        Route::post('/logout', 'logout')->name('auth.logout');
        Route::post('/refresh', 'refreshToken')->name('auth.refresh');
    });
});

Route::group(['prefix' => 'user', 'middleware' => ['IjpAuth']], function () {
    Route::controller(UserController::class)->group(function () {
        Route::patch('/{id}', 'update')->name('user.update');
        Route::patch('/role/{id}', 'updateRoleUser')->name('user.updateRole');
        Route::get('/{id}', 'show')->name('user.show');
        Route::get('/', 'showAll')->name('user.showAll');
        Route::delete('/{id}', 'delete')->name('user.delete');
        Route::post('/register', 'register')->name('user.register');
    });
});
