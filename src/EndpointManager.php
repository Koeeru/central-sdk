<?php

namespace Koeeru\Central;

class EndpointManager
{
    protected string $baseUrl;

    public function __construct(string $baseUrl = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? config('central.url'), '/');
    }

    public function getUserInfoEndpoint(): string
    {
        return $this->baseUrl . '/api/auth/user-info';
    }

    public function getLogoutEndpoint(): string
    {
        return $this->baseUrl . '/api/auth/logout';
    }

    public function getUserInfoByIdEndpoint($id): string
    {
        return $this->baseUrl . '/api/users/' . $id . '?appKey=' . config('central.app_id');
    }

    public function getUserListEndpoint($queries): string
    {
        return $this->baseUrl . '/api/users/search?' . $queries;
    }

    public function getAttachUserPermission(): string
    {
        return $this->baseUrl . '/api/user/attach/permissions';
    }

    public function getDetachUserPermission(): string
    {
        return $this->baseUrl . '/api/user/detach/permissions';
    }

}

