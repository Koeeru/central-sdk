<?php

namespace Koeeru\Central\Traits;

use Koeeru\Central\Transformers\UserTransformer;

trait UserHelpers
{
    protected function transformUser(array $user): array
    {
        return UserTransformer::transform($user);
    }
}
