<?php

namespace Koeeru\Central\Console\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Koeeru\Central\EndpointManager;
use Koeeru\Central\Services\OAuthClientCredentialsTokenService;

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
     * Xử lý middleware pipeline cho command
     *
     * @param mixed $command
     * @param Closure $next
     * @return mixed
     */
    public function handle($command, Closure $next)
    {
        $token = app(OAuthClientCredentialsTokenService::class)->getToken();

        if (!$token) {
            $command->error('Could not retrieve OAuth client_credentials token. Please check your Central Server configuration.');
            return 1;
        }

        return $next($command);
    }

}
