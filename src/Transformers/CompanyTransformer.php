<?php

namespace Koeeru\Central\Transformers;

use Koeeru\Central\Contracts\TransformerInterface;

class CompanyTransformer implements TransformerInterface
{

    public static function transform(array $item): array
    {
        return $item;
    }
}
