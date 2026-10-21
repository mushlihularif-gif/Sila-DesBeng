<?php

namespace App\Services;

use Illuminate\Http\Request;

class ChatSessionToken
{
    public static function read(Request $request): ?string
    {
        $token = $request->header('X-Chat-Session-Token') ?: $request->input('session_token');
        // Token lama buatan browser (Math.random) tidak lagi dapat menjadi kredensial.
        return is_string($token) && preg_match('/\A[a-zA-Z0-9]{64}\z/', $token) ? $token : null;
    }
}
