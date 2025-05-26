<?php

namespace Koeeru\Central\PubSub\Dispatcher;

use Koeeru\Central\Contracts\HandlerRegistryInterface;
use Koeeru\Central\PubSub\Handlers\CompanyCreatedHandler;

class EventHandlerRegistry implements HandlerRegistryInterface
{
    public function registerHandlers(EventDispatcher $dispatcher): void
    {
        $dispatcher->register('company.created', new CompanyCreatedHandler());

    }
}
