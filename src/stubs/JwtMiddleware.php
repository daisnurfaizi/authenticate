<?php

namespace App\Http\Middleware;

use Closure;
use MyLibrary\JwtAuth\JwtAuth;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        $jwtAuth = app(JwtAuth::class);
        $user = $jwtAuth->validateToken($token, new \App\Models\User());

        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // Attach user to request
        $request->merge(['user' => $user]);
        return $next($request);
    }
}
