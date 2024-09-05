<?php

namespace Koeeru\Central\Helpers;

class AttributeConverter
{
    public static function convert(array $attributes): array
    {
        $result = [];
        foreach ($attributes as $item) {
            $result[$item['attributeKey']] = $item['attributeValue'];
        }

        return $result;
    }
}
