<?php

namespace Koeeru\Central;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Koeeru\Central\Services\OAuthClientCredentialsTokenService;

class ApiCaller
{
    protected ?string $bearerToken;

    public function __construct(Request $request, bool $validateBearerToken = true)
    {
        $this->bearerToken =
            $request->bearerToken()
            ?? $request->cookie('token')
            ?? $request->header('Authorization')
            ?? app(OAuthClientCredentialsTokenService::class)->getToken();

        if ($validateBearerToken) {
            $this->validateBearerToken($this->bearerToken);
        }
    }

    protected function validateBearerToken(?string $token): void
    {
        if (empty($token)) {
            Log::error('Bearer token is missing or empty');
        }
    }

    protected function getDefaultHttpOptions(): array
    {
        return [
            'timeout' => 60,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];
    }

    public function get(string $url): array
    {
        try {
            $response = Http::withHeaders(['Authorization' => "Bearer {$this->bearerToken}"])
                ->withOptions($this->getDefaultHttpOptions())
                ->get($url);

            $response->throw();

            return $response->json();
        } catch (\Exception $e) {
            Log::info("Fetching URL: {$url}");
            Log::error("Error fetching URL: {$e->getMessage()}");
            throw $e;
        }
    }

    public function post(string $url, array $data = []): array
    {
        try {
            $response = Http::withHeaders(['Authorization' => "Bearer {$this->bearerToken}"])
                ->withOptions($this->getDefaultHttpOptions())
                ->post($url, $data);

            $response->throw();

            return $response->json();
        } catch (\Exception $e) {
            Log::info("Fetching URL: {$url}");
            Log::error("Error posting to URL: {$e->getMessage()}");
            throw $e;
        }
    }


    public function put(string $url, array $data): array
    {

    }

    public function delete(string $url, array $data): array
    {
        try {
            $response = Http::withHeaders(['Authorization' => "Bearer {$this->bearerToken}"])
                ->withOptions($this->getDefaultHttpOptions())
                ->delete($url, $data);

            $response->throw();

            return $response->json();
        } catch (\Exception $e) {
            Log::error("Error posting to URL: {$e->getMessage()}");
            throw $e;
        }
    }
}
