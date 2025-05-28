<?php

namespace Koeeru\Central\Services;

class OAuthClientCredentialsTokenService
{
    public function __construct(public ?string $token = null) {}

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }
}
