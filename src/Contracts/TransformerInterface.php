<?php

namespace Koeeru\Central\Contracts;

interface TransformerInterface
{
    public static function transform(array $item): array;
}
