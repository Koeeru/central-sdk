<?php

namespace Koeeru\Central\Contracts;

use Koeeru\Central\PubSub\Dispatcher\EventDispatcher;

interface HandlerRegistryInterface
{
    public function registerHandlers(EventDispatcher $dispatcher): void;

}
