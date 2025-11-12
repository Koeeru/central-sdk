<?php


use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::group(['middleware' => config('central.middleware')], function () {
        Route::get('user-info', [\Koeeru\Central\Http\AuthController::class, 'user']);
        Route::post('logout', [\Koeeru\Central\Http\AuthController::class, 'logout']);
    });
});

Route::post('exchange-token', \Koeeru\Central\Http\ExchangeTokenAction::class);
