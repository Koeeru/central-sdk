<?php

namespace Koeeru\Central\PubSub\Commands;

use Illuminate\Console\Command;
use Koeeru\Central\Contracts\SubscriberInterface;

class CentralChannelSubscriberCommand extends Command
{
    protected $signature = 'central:channel:subscriber';


    public function __construct(protected SubscriberInterface $subscriber)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->subscriber->subscribe();
    }
}
