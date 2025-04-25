<?php

namespace Koeeru\Central\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController
{
    public function user(Request $request)
    {
        return \response()->json([
            'data' => $request->user(),
        ]);
    }

    public function logout()
    {
        Auth::logout();

        return \response()->json([
            'data' => null,
            'message' => 'Logged out successfully.',
        ]);
    }
}
