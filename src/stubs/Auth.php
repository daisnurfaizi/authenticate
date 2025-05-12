<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login')->name('auth.login');
        // Route::post('/register', 'register')->name('auth.register');
        Route::post('/logout', 'logout')->name('auth.logout');
    });
});
