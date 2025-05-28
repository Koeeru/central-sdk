<?php

namespace Koeeru\Central\Providers;

use App\Contracts\PubSub\ApplicationPublisherInterface;
use App\Services\PubSub\RedisApplicationPublisher;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Koeeru\Central\Contracts\SubscriberInterface;
use Koeeru\Central\Guards\RemoteGuard;
use Koeeru\Central\PubSub\Dispatcher\EventDispatcher;
use Koeeru\Central\PubSub\Subscribers\RedisSubscriber;
use Koeeru\Central\Services\AuthService;
use Koeeru\Central\Services\OAuthClientCredentialsTokenService;

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


        $this->app->bind(SubscriberInterface::class, function ($app) {
            $driver = config('central.pubsub.driver');

            $dispatcher = new EventDispatcher();

            $registryClass = config('central.pubsub.handler_registry');

            if (!class_exists($registryClass)) {
                throw new InvalidArgumentException("Handler registry class [{$registryClass}] does not exist.");
            }

            $registry = $app->make($registryClass);

            if (!method_exists($registry, 'registerHandlers')) {
                throw new InvalidArgumentException("Handler registry [{$registryClass}] must have a method registerHandlers().");
            }

            $registry->registerHandlers($dispatcher);

            return match ($driver) {
                'redis' => new RedisSubscriber($dispatcher),
                // 'kafka' => new KafkaSubscriber(),
                default => throw new \InvalidArgumentException("Unsupported pubsub driver [$driver]"),
            };
        });


        $this->app->singleton(OAuthClientCredentialsTokenService::class, function () {
            return new OAuthClientCredentialsTokenService();
        });
    }
}
