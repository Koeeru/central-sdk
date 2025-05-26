<?php

namespace Koeeru\Central\PubSub\Subscribers;

use Koeeru\Central\Contracts\SubscriberInterface;
use Koeeru\Central\PubSub\Dispatcher\EventDispatcher;
use Illuminate\Support\Facades\Redis;

class RedisSubscriber implements SubscriberInterface
{
    public function __construct(
        protected EventDispatcher $dispatcher
    ) {}

    public function subscribe(): void
    {
        $clientId = config('central.app_id');

        $channelPattern = "app.{$clientId}.*";

        Redis::psubscribe([$channelPattern], function ($message, $channel) {
            $payload = json_decode($message, true);
            $event = $payload['event'] ?? null;

            if (! $event) return;

            $this->dispatcher->dispatch($event, $payload);
        });
    }
}
