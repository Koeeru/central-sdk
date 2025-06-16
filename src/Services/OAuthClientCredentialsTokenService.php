<?php

namespace Koeeru\Central\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Koeeru\Central\EndpointManager;

class OAuthClientCredentialsTokenService
{
    protected EndpointManager $endpointManager;

    public function __construct(public ?string $token = null) {
        $this->endpointManager = app(EndpointManager::class);
    }


    protected function cacheKey(): string
    {
        $clientId = config('central.app_id');
        return "central:oauth:{$clientId}:access_token";
    }


    public function getToken(): ?string
    {
        if (App::environment('testing')) {
            return 'test-token';
        }

        $token = Cache::get($this->cacheKey());

        if (!$token) {
            $token = $this->fetchAccessToken();

            if (!$token) {
                throw new \RuntimeException('Unable to retrieve OAuth client_credentials token from central server.');
            }

            Cache::put(
                $this->cacheKey(),
                $token['access_token'],
                now()->addSeconds($token['expires_in'] - 60) // trừ 60s phòng token hết hạn sớm
            );
            $token = $token['access_token'];
        }

        return $token;
    }

    protected function fetchAccessToken(): ?array
    {
        $response = Http::asForm()->post($this->endpointManager->fetchAccessTokenEndpoint(), [
            'grant_type'    => 'client_credentials',
            'client_id'     => config('central.app_id'),
            'client_secret' => config('central.app_secret'),
            'scope'         => '',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function refreshToken(): ?string
    {
        $token = $this->fetchAccessToken();

        if (!$token) {
            throw new \RuntimeException('Unable to refresh OAuth client_credentials token from central server.');
        }

        Cache::put(
            $this->cacheKey(),
            $token['access_token'],
            now()->addSeconds($token['expires_in'] - 60) // trừ 60s phòng token hết hạn sớm
        );

        return $token['access_token'];
    }
}
