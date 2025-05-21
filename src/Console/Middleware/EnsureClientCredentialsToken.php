<?php

namespace Koeeru\Central\Console\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Koeeru\Central\EndpointManager;

/**
 * Middleware đảm bảo token OAuth2 loại client_credentials được lấy từ Central Server,
 * lưu cache theo chuẩn, và cung cấp token cho các command gọi API server-to-server.
 */
class EnsureClientCredentialsToken
{
    public function __construct(protected EndpointManager $endpointManager)
    {
    }

    /**
     * Lấy cache key theo chuẩn: services:central:oauth:{client_id}:access_token
     */
    protected function cacheKey(): string
    {
        $clientId = config('central.app_id');
        return "central:oauth:{$clientId}:access_token";
    }

    /**
     * Xử lý middleware pipeline cho command
     *
     * @param mixed $command
     * @param Closure $next
     * @return mixed
     */
    public function handle($command, Closure $next)
    {
        $token = Cache::get($this->cacheKey());

        if (!$token) {
            $token = $this->fetchAccessToken();

            if (!$token) {
                $command->error('Unable to retrieve OAuth client_credentials token from central server.');
                return 1; // exit code báo lỗi
            }

            Cache::put(
                $this->cacheKey(),
                $token['access_token'],
                now()->addSeconds($token['expires_in'] - 60) // trừ 60s phòng token hết hạn sớm
            );
            $token = $token['access_token'];
        }

        // Đăng ký token trong service container để các phần khác lấy dùng
        app()->instance('oauth.client_credentials_token', $token);

        return $next($command);
    }

    /**
     * Gọi API lấy access token từ central server qua client_credentials
     *
     * @return array|null ['access_token' => string, 'expires_in' => int] hoặc null nếu lỗi
     */
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
}
