<?php

namespace Koeeru\Central\Guards;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Http\Request;
use Koeeru\Central\Services\AuthService;

class RemoteGuard implements Guard
{
    use GuardHelpers;

    public function __construct(
        protected AuthService $authService,
        protected Request     $request
    )
    {
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $bearerToken = $this->request->bearerToken();

        if (!$bearerToken) {
            return null;
        }

        $user = $this->authService->retrieveByToken();

        if ($user) {
            $this->user = $user;
        }

        return $this->user;
    }


    public function validate(array $credentials = [])
    {

    }


    public function logout(): void
    {
        $this->authService->logout();
    }

}
