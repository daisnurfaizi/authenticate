<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Helper\ResponseJsonFormater;

class AuthController extends Controller
{
    /**
     * Login user and generate access and refresh tokens.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {

            // Cek apakah pengguna sudah terautentikasi

            // Verifikasi kredensial
            if (!JWTAuth::attempt($credentials)) {
                return ResponseJsonFormater::error(
                    code: 401,
                    message: 'Email or password is incorrect'
                );
            }
        }
        // catch if validation request faild 
        catch (ValidationException $e) {
            return ResponseJsonFormater::error(
                code: 422,
                message: 'Validation error',
                // data: $e->validator->errors()
            );
        } catch (JWTException $e) {
            return ResponseJsonFormater::error(
                code: 500,
                message: 'Oops, something went wrong'
            );
        }

        return ResponseJsonFormater::success(
            message: "Sucess Login",
            data: [
                'id' => Auth::user()->id,
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'role' => Auth::user()->role,
            ]
        )
            ->withCookie('access_token', $this->createToken(
                [
                    'email' => $request->email,
                    'role' => Auth::user()->role,
                ]
            ), 60)
            ->withCookie('refresh_token', $this->createToken(
                [
                    'email' => $request->email,
                    'role' => Auth::user()->role,
                ]
            ), 60 * 24 * 30);
    }

    public function logout(Request $request)
    {

        $token = $request->cookie('access_token');

        if (!$token) {
            throw new \Exception("Token not provided in cookie");
        }

        // Invalidate token
        JWTAuth::setToken($token)->invalidate();

        return ResponseJsonFormater::success(
            message: 'Logout successful'
        )->withCookie(cookie()->forget('access_token'))
            ->withCookie(cookie()->forget('refresh_token'));
    }

    /**
     * Create a JWT token with custom payload.
     */
    public function createToken(array $customPayload = [])
    {
        // create payload for token
        $payload = JWTAuth::factory()->customClaims($customPayload)->make();
        // create token
        $token = JWTAuth::fromUser(Auth::user(), $payload);
        // set token to cookies
        return $token;
    }
}
