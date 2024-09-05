<?php

namespace Koeeru\Central\Contracts;

interface CentralServerInterface
{
    public function getUser(): null|array;
    public function logout(): bool;
    public function getUserInfo(string $id): null|array;
    public function getUserList(array $queries): null|array;
    public function attachUserPermission(array $data): null|array;
    public function detachUserPermission(array $data): null|array;
}
