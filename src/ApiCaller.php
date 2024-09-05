<?php

namespace Koeeru\Central;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiCaller
{
    protected string $bearerToken;

    public function __construct(Request $request)
    {
        $this->validateBearerToken($request->bearerToken());
        $this->bearerToken = $request->bearerToken();
    }

    protected function validateBearerToken(?string $token): void
    {
        if (empty($token)) {
            throw new \InvalidArgumentException("Bearer token is missing or invalid");
        }
    }

    protected function getDefaultHttpOptions(): array
    {
        return [
            'timeout' => 10,
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
