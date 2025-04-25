<?php

namespace Koeeru\Central\Http;

use Illuminate\Http\Request;
use Koeeru\Central\CentralServer;

class ExchangeTokenAction
{
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'grant_type' => 'required|string',
            'client_id' => 'required|string',
            'redirect_uri' => 'required|string',
            'code' => 'required|string',
        ]);

        if($validated['grant_type'] !== 'authorization_code') {
            return response()->json([
                'error' => 'unsupported_grant_type',
                'error_description' => 'The authorization grant type is not supported by the authorization server.',
            ], 400);
        }

        $payload = [
            'grant_type' => 'authorization_code',
            'client_id' => $validated['client_id'],
            'client_secret' => config('central.app_secret'),
            'redirect_uri' => $validated['redirect_uri'],
            'code' => $validated['code'],
        ];

        try {
            $centralServe = app()->make(CentralServer::class);
            $data = $centralServe->exchangeToken($payload);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'server_error',
                'error_description' => $e->getMessage(),
            ], 500);
        }
    }
}
