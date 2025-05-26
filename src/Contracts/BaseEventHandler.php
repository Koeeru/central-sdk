<?php

namespace Koeeru\Central\Contracts;

interface BaseEventHandler
{
    public function handle(?array $payload): void;
}
