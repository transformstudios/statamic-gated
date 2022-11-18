<?php

use Illuminate\Support\Facades\Route;
use TransformStudios\Gated\Http\Controllers\PasswordController;

Route::name('gated.')->group(function () {
    Route::name('password.')->group(function () {
        Route::view('password', 'gated::password')->name('show');
        Route::post('validate', [PasswordController::class, 'store'])->name('store');
    });
});
