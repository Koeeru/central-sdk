<?php

namespace Koeeru\Central\Contracts;

interface PermissionInterface
{
    public function hasAnyRole(...$roles): bool;
    public function hasRole($roles, string $guard = null): bool;
}
