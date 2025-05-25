<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Ijp\Auth\Repositories\UserRepository;
use Ijp\Auth\Service\AuthService;

class AuthController extends Controller
{
    protected $authService;


    public function __construct()
    {
        $this->authService = new AuthService(new UserRepository(new User()));
    }
    /**
     * Login user and generate access and refresh tokens.
     */
    public function login(Request $request)
    {
        return $this->authService->login($request);
    }

    public function logout(Request $request)
    {

        return $this->authService->logout($request);
    }

    public function refreshToken(Request $request)
    {
        return $this->authService->refresh($request);
    }
}
