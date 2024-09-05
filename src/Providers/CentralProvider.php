<?php

namespace Koeeru\Central\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Koeeru\Central\Guards\RemoteGuard;
use Koeeru\Central\Services\AuthService;

class CentralProvider extends ServiceProvider
{
    public function boot()
    {
        Auth::extend('remote', function ($app) {
            return new RemoteGuard($app->make(AuthService::class), $app->make('request'));
        });


        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('central.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'central');
    }
}
