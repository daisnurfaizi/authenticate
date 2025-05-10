<?php

namespace Ijp\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuth
{
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function generateToken(array $payload): string
    {
        return JWT::encode($payload, $this->key, 'HS256');
    }

    public function validateToken(string $token): object
    {
        return JWT::decode($token, new Key($this->key, 'HS256'));
    }

    public function generateAccessTokenCookie(array $payload, int $expiration = 3600): Cookie
    {
        $token = $this->generateToken($payload);
        return new Cookie(
            'access_token',
            $token,
            time() + $expiration,
            '/',
            null,
            true, // Secure: true for HTTPS
            true, // HttpOnly
            false, // Raw
            'Strict' // SameSite policy
        );
    }

    public function generateRefreshTokenCookie(array $payload, int $expiration = 604800): Cookie
    {
        $token = $this->generateToken($payload);
        return new Cookie(
            'refresh_token',
            $token,
            time() + $expiration,
            '/',
            null,
            true, // Secure: true for HTTPS
            true, // HttpOnly
            false, // Raw
            'Strict' // SameSite policy
        );
    }
}
