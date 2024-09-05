<?php

namespace Koeeru\Central\Helpers;

class ExternalIdFinder
{
    public static function find(array $mapping, string $appKey): ?int
    {
        foreach ($mapping as $item) {
            if ($item['appKey'] === $appKey) {
                return $item['externalId'];
            }
        }

        return null;
    }
}
