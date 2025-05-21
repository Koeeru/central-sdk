<?php

namespace Koeeru\Central\Console;

use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use Koeeru\Central\Console\Middleware\EnsureClientCredentialsToken;

abstract class CentralBaseCommand extends Command
{
    protected array $middleware = [];

    protected bool $useDefaultMiddleware = true;

    protected function defaultMiddleware(): array
    {
        return [
            EnsureClientCredentialsToken::class,
        ];
    }

    protected function resolveMiddleware(): array
    {
        return $this->useDefaultMiddleware
            ? array_merge($this->defaultMiddleware(), $this->middleware)
            : $this->middleware;
    }

    public function handle()
    {
        return app(Pipeline::class)
            ->send($this)
            ->through($this->resolveMiddleware())
            ->then(function ($command) {
                return $command->handleCommand();
            });
    }


    abstract public function handleCommand();
}
