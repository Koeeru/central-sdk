<?php

namespace Koeeru\Central\PubSub\Dispatcher;

use Koeeru\Central\Contracts\BaseEventHandler;

class EventDispatcher
{
    protected array $handlers = [];

    public function register(string $event, BaseEventHandler $handler): void
    {
        $this->handlers[$event] = $handler;
    }

    public function dispatch(string $event, array $payload): void
    {
        if (! isset($this->handlers[$event])) {
            throw new \RuntimeException("No handler registered for event: {$event}");
        }

        $this->handlers[$event]->handle($payload);
    }
}
