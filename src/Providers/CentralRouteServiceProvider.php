<?php

namespace Koeeru\Central\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

class CentralRouteServiceProvider extends RouteServiceProvider
{
    public function boot()
    {
        $this->routes(function () {
            Route::prefix('central-api')
                ->middleware('api')
                ->group(__DIR__.'/../routes.php');
        });
    }
}
