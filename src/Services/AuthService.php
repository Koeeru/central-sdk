<?php

namespace Koeeru\Central\Services;

use Koeeru\Central\Models\User;
use Koeeru\Central\CentralServer;
use Koeeru\Central\Traits\UserHelpers;

class AuthService
{
    use UserHelpers;

    public function __construct(protected CentralServer $server)
    {
    }

    public function retrieveByToken(): ?User
    {
        try {
            $user = $this->server->getUser();

            if (empty($user)) {
                return null;
            }

            $userModelClass = config('central.models.user');

            if (!class_exists($userModelClass)) {
                throw new \RuntimeException("User model class '{$userModelClass}' not found.");
            }


            $transformedUser = $this->transformUser($user);

            return $userModelClass::make($transformedUser);
        } catch (\Exception $e) {
            throw new \RuntimeException("Error retrieving user from central server: {$e->getMessage()}");
        }
    }

    public function logout(): bool
    {
        return $this->server->logout();
    }

}
