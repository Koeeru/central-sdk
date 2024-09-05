<?php

namespace Koeeru\Central;

use Illuminate\Support\Facades\Log;
use Koeeru\Central\Contracts\CentralServerInterface;

class CentralServer implements CentralServerInterface
{
    protected ApiCaller $apiCaller;

    protected EndpointManager $endpointManager;

    public function __construct(ApiCaller $apiCaller, EndpointManager $endpointManager)
    {
        $this->apiCaller = $apiCaller;
        $this->endpointManager = $endpointManager;
    }

    public function getUser(): null|array
    {
        try {
            $response = $this->apiCaller->get($this->endpointManager->getUserInfoEndpoint());

            return $response['data'];
        } catch (\Exception $e) {
            Log::error("Error fetching user information: {$e->getMessage()}");
            return null;
        }
    }

    public function logout(): bool
    {
        try {
            $this->apiCaller->post($this->endpointManager->getLogoutEndpoint());

            return true;
        } catch (\Exception $e) {
            Log::error("Error logging out user: {$e->getMessage()}");
            return false;
        }
    }

    public function getUserInfo($id): null|array
    {
        try {
            $response = $this->apiCaller->get($this->endpointManager->getUserInfoByIdEndpoint($id));

            return $response['data'];
        } catch (\Exception $e) {
            Log::error("Error fetching user information: {$e->getMessage()}");
            return null;
        }
    }

    public function getUserList($queries): null|array
    {
        try {
            $response = $this->apiCaller->get($this->endpointManager->getUserListEndpoint(http_build_query($queries)));

            return $response['data'];
        } catch (\Exception $e) {
            Log::error("Error fetching user list: {$e->getMessage()}");
            return null;
        }
    }

    public function attachUserPermission($data): null|array
    {
        try {
            $response = $this->apiCaller->post($this->endpointManager->getAttachUserPermission(), $data);

            return $response['data'];
        } catch (\Exception $e) {
            Log::error("Error attaching user permissions: {$e->getMessage()}");
            return null;
        }
    }

    public function detachUserPermission($data): null|array
    {
        try {
            $response = $this->apiCaller->delete($this->endpointManager->getDetachUserPermission(), $data);

            return $response['data'];
        } catch (\Exception $e) {
            Log::error("Error detaching user permissions: {$e->getMessage()}");
            return null;
        }
    }
}
