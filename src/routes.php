<?php


use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('user-info', [\App\Http\Controllers\AuthController::class, 'user']);
        Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout']);
    });
});

Route::post('exchange-token', \Koeeru\Central\Http\ExchangeTokenAction::class);
