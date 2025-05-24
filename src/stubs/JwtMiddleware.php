<?php

namespace App\Http\Middleware;

use App\Helper\ResponseJsonFormater;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Periksa apakah cookie access_token atau refresh_token ada
        if (!$request->hasCookie('access_token') || !$request->hasCookie('refresh_token')) {
            return ResponseJsonFormater::error(
                code: 401,
                message: 'Unauthorized'
            );
        }

        // Ambil access_token dari cookie
        $accessToken = $request->cookie('access_token');
        try {
            // Verifikasi apakah access token valid
            if (!JWTAuth::setToken($accessToken)->check()) {
                return ResponseJsonFormater::error(
                    code: 401,
                    status: 'invalid',
                    message: 'Unauthorized: Invalid or expired access token'
                );
            }



            // Jika token valid, kita bisa mendapatkan payloadnya
            $payload = JWTAuth::setToken($accessToken)->getPayload();
            $dataPayload = $payload->toArray();
            $user = User::select('access_token', 'access_token_expired_at')->where('username', $dataPayload['username'])->first();
            if (!$user) {
                return ResponseJsonFormater::error(
                    code: 401,
                    message: 'Unauthorized: access token not found'
                );
            }

            // Cek apakah access token sudah kadaluarsa
            if ($user->access_token !== $dataPayload['access_token'] || $user->access_token_expired_at < now()) {
                return ResponseJsonFormater::error(
                    code: 401,
                    message: 'Unauthorized: Access token expired or invalid'
                );
            }

            //  check apakah token sudah kadaluarsa
        } catch (JWTException $e) {
            // Tangani error jika ada masalah dengan token (misalnya token kadaluarsa atau tidak valid)
            return ResponseJsonFormater::error(
                code: 401,
                message: 'Unauthorized: Token error'
            );
        }
        return $next($request);
    }
}
