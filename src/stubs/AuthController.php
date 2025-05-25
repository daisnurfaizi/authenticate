<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Ijp\Auth\Repositories\UserRepository;
use Ijp\Auth\Service\UserService;

class AuthController extends Controller
{
    protected $userService;


    public function __construct()
    {
        $this->userService = new UserService(new UserRepository(new User()));
    }
    /**
     * Login user and generate access and refresh tokens.
     */
    public function login(Request $request)
    {
        return $this->userService->login($request);
    }

    public function logout(Request $request)
    {

        return $this->userService->logout($request);
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

    public function refreshToken(Request $request)
    {
        return $this->userService->refresh($request);
    }
}
