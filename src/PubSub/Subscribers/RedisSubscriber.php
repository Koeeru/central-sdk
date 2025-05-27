<?php

namespace Koeeru\Central\PubSub\Subscribers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis as RedisFacade;
use Koeeru\Central\Contracts\SubscriberInterface;
use Koeeru\Central\PubSub\Dispatcher\EventDispatcher;
use Redis;

class RedisSubscriber implements SubscriberInterface
{
    protected string $clientId;

    public function __construct(
        protected EventDispatcher $dispatcher
    ) {
        $this->clientId = config('central.app_id');
    }

    public function subscribe(): void
    {
        $channels = $this->getSubscribedChannels();
        $redis = $this->getRedisClient();

        try {
            $redis->subscribe($channels, function (Redis $redisClient, string $channel, string $message) {
                $this->handleMessage($channel, $message);
            });
        } catch (\Throwable $e) {
            Log::error("[{$this->clientId}] ❌ Redis subscribe failed: " . $e->getMessage());
        }
    }

    /**
     * Get full list of channel names this client will subscribe to.
     */
    protected function getSubscribedChannels(): array
    {
        $channels = config('central.pubsub.channels', []);

        return collect($channels)
            ->map(fn(string $channel) => "app:{$this->clientId}:{$channel}")
            ->toArray();
    }

    /**
     * Handle an incoming message on a Redis channel.
     */
    protected function handleMessage(string $channel, string $message): void
    {
        Log::info("[{$this->clientId}] 📩 Received on channel [$channel]: $message");

        $payload = json_decode($message, true);

        if (!is_array($payload)) {
            Log::warning("[{$this->clientId}] ⚠️ Invalid JSON payload on $channel");
            return;
        }

        $event = $payload['event'] ?? null;

        if (!$event) {
            Log::warning("[{$this->clientId}] ⚠️ No 'event' field in payload on $channel");
            return;
        }

        try {
            $this->dispatcher->dispatch($event, $payload);
        } catch (\Throwable $e) {
            Log::error("[{$this->clientId}] ❌ Event dispatch failed for [$event] on [$channel]: " . $e->getMessage());
        }
    }

    /**
     * Get configured Redis client with correct options.
     */
    protected function getRedisClient(): Redis
    {
        $redis = RedisFacade::connection()->client();
        $redis->setOption(Redis::OPT_PREFIX, '');
        $redis->setOption(Redis::OPT_READ_TIMEOUT, 60); // Optional: avoid timeout if idle
        return $redis;
    }
}
