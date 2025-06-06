<?php

namespace Ijp\Auth\Service;

use App\Helper\ResponseJsonFormater;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Ijp\Auth\Traits\PaginateResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;

class AuthService
{
    use PaginateResolver;
    protected $userRepository;

    public function __construct($userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Handle user login and generate tokens.
     *
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        try {
            // Verify credentials
            if (!JWTAuth::attempt($credentials)) {
                return ResponseJsonFormater::error(
                    code: 401,
                    message: 'Username or password is incorrect'
                );
            }

            $user = Auth::user();
            $permissions = $user->role && $user->role->permissions
                ? $user->role->permissions->map(function ($roleHasPermission) {
                    return [
                        'id' => $roleHasPermission->permission->id ?? null,
                        'name' => $roleHasPermission->permission->name ?? null,
                    ];
                })
                : [];

            // Create tokens
            $accessToken = $this->generateToken($request, tokenType: 'access_token'); // Access token valid for 30 minutes
            $refreshToken = $this->generateToken($request, tokenType: 'refresh_token');

            return ResponseJsonFormater::success(
                message: "Success Login",
                data: [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role ? [
                        'id' => $user->role->id ?? null,
                        'name' => $user->role->name ?? null,
                    ] : null,
                    'permissions' => $permissions,
                ]
            )
                ->withCookie('access_token', $accessToken, 30)
                ->withCookie('refresh_token', $refreshToken, 60 * 24 * 7); // Refresh token valid for 7 days
        } catch (ValidationException $e) {
            return ResponseJsonFormater::error(
                code: 422,
                message: 'Validation error',
            );
        } catch (JWTException $e) {
            return ResponseJsonFormater::error(
                code: 500,
                message: $e->getMessage(),
            );
        } catch (\Exception $e) {
            return ResponseJsonFormater::error(
                code: 500,
                message: $e->getMessage(),
            );
        }
    }

    /**
     * Generate a token for the user.
     *
     * @param Request $request
     * @param string $tokenType
     * @return string
     * @throws \Exception
     */
    private function generateToken(Request $request, string $tokenType): string
    {
        try {
            DB::beginTransaction();

            $token = bin2hex(random_bytes(16)); // Token unik

            if ($tokenType === 'access_token') {
                $this->userRepository->storeAccessToken('username', $request->username, $token);
                DB::commit();

                // Membuat access token dengan payload baru
                return $this->createToken([
                    'access_token' => $token,
                ], 'access_token');
            } elseif ($tokenType === 'refresh_token') {
                $refreshToken = $this->userRepository->storeRefreshToken('username', $request->username, $token);
                DB::commit();
                // Membuat refresh token dengan payload baru
                $tokenRefresh = [
                    'refresh_token' => $refreshToken->refresh_token,
                    'username' => $refreshToken->username,
                    'iat' => time(), // Waktu saat ini
                    'exp' => $refreshToken->refresh_token_expired_at->timestamp, // Token kedaluwarsa sesuai waktu yang ditentukan
                ];
                return JWT::encode($tokenRefresh, env('JWT_SECRET'), 'HS256');
            } else {
                throw new \Exception('Invalid token type', 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Failed to create {$tokenType}", 500);
        }
    }




    /**
     * Create a JWT token with custom payload.
     *
     * @param array $customPayload
     * @return string
     */
    public function createToken(array $customPayload = [], string $tokenType = 'access_token')
    {
        // Klaim dasar
        $baseClaims = [
            'iss' => url()->current(),
            'iat' => now()->timestamp,
            'nbf' => now()->timestamp,
            'exp' => $tokenType === 'access_token'
                ? now()->addMinutes(30)->timestamp
                : now()->addDays(7)->timestamp,
            'jti' => bin2hex(random_bytes(8)),
            'sub' => Auth::user()->id,
            'id' => Auth::user()->id,
            'name' => Auth::user()->name,
            'username' => Auth::user()->username,
            'prv' => bin2hex(random_bytes(20)),
        ];

        // Tambahkan klaim khusus berdasarkan tipe token
        if ($tokenType === 'refresh_token') {
            unset($baseClaims['access_token']); // Pastikan tidak ada access_token
            $baseClaims['refresh_token'] = $customPayload['refresh_token'] ?? bin2hex(random_bytes(16));
        } elseif ($tokenType === 'access_token') {
            $baseClaims['access_token'] = $customPayload['access_token'] ?? bin2hex(random_bytes(16));
        }

        // Buat token
        $payload = JWTAuth::factory()->customClaims($baseClaims)->make();
        return JWTAuth::encode($payload)->get();
    }






    /**
     * Handle user logout.
     *
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {
        try {
            DB::beginTransaction();

            $accessToken = $request->cookie('access_token');
            if (!$accessToken) {
                return ResponseJsonFormater::error(
                    code: 401,
                    message: 'Token not provided in cookie'
                );
            }

            $payload = JWTAuth::setToken($accessToken)->getPayload();
            $this->userRepository->revokeRefreshToken('username', $payload['username']);
            $this->userRepository->revokeAccessToken('username', $payload['username']);

            DB::commit();

            return ResponseJsonFormater::success(
                message: 'Logout successful'
            )->withCookie(cookie()->forget('access_token'))
                ->withCookie(cookie()->forget('refresh_token'));
        } catch (JWTException | \Exception) {
            DB::rollBack();

            return ResponseJsonFormater::error(
                code: 500,
                message: 'Failed to logout'
            )->withCookie(cookie()->forget('access_token'))
                ->withCookie(cookie()->forget('refresh_token'));
        }
    }

    /**
     * Refresh the access token using a refresh token.
     *
     * @param Request $request
     * @return mixed
     */
    public function refreshAccessToken(Request $request)
    {
        try {
            $refreshToken = $request->cookie('refresh_token');

            if (!$refreshToken) {
                return ResponseJsonFormater::error(
                    code: 401,
                    message: 'Refresh token not provided in cookie'
                );
            }

            $payload = JWTAuth::setToken($refreshToken)->getPayload();
            $payloadUser = $request->payload;

            // Check if the refresh token is expired
            if ($payload['exp'] < time()) {
                return ResponseJsonFormater::error(
                    code: 401,
                    message: 'Refresh token expired'
                )->withCookie(cookie()->forget('access_token'))
                    ->withCookie(cookie()->forget('refresh_token'));
            }

            // Validate refresh token
            $validate = $this->userRepository->validateRefreshToken(
                'username',
                $payloadUser['username'],
                $payload['refresh_token']
            );

            if (!$validate) {
                return ResponseJsonFormater::success(
                    message: 'Logout successful'
                )->withCookie(cookie()->forget('access_token'))
                    ->withCookie(cookie()->forget('refresh_token'));
            }

            $newUser = $this->userRepository->storeRefreshToken(
                'username',
                $payloadUser['username'],
                $payload['refresh_token']
            );

            $newAccessToken = $this->createToken([
                'username' => $newUser->username,
                'role' => $newUser->role_id,
            ]);

            return ResponseJsonFormater::success(
                message: 'Access token refreshed successfully'
            )->withCookie('access_token', $newAccessToken, 60);
        } catch (JWTException | \Exception) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return ResponseJsonFormater::error(
                code: 500,
                message: 'Failed to refresh access token'
            )->withCookie(cookie()->forget('access_token'))
                ->withCookie(cookie()->forget('refresh_token'));
        }
    }

    public function refresh($request)
    {
        try {
            $refreshToken = $request->cookie('refresh_token');
            $accessToken = $request->cookie('access_token');

            if (!$refreshToken) {
                return ResponseJsonFormater::error(
                    code: 401,
                    message: 'Refresh token not provided in cookie'
                )
                    ->withCookie(cookie()->forget('access_token'))
                    ->withCookie(cookie()->forget('refresh_token'));
            }

            $secret = env('JWT_SECRET');
            $payload = JWT::decode($refreshToken, new Key($secret, 'HS256'));

            if (isset($payload->exp) && $payload->exp < time()) {
                return ResponseJsonFormater::error(
                    code: 401,
                    message: 'Refresh token expired'
                )->withCookie(cookie()->forget('access_token'))
                    ->withCookie(cookie()->forget('refresh_token'));
            }

            $user = $this->userRepository->showBy('username', $payload->username);
            if (
                !$user || $user->refresh_token_expired_at < now()
            ) {
                return ResponseJsonFormater::error(
                    code: 404,
                    message: 'User not found or refresh token expired'
                )->withCookie(cookie()->forget('access_token'))
                    ->withCookie(cookie()->forget('refresh_token'));
            }

            // Generate new tokens
            $newTokenAccess = $this->userRepository->storeAccessToken('username', $user->username, bin2hex(random_bytes(16)));
            $newTokenRefresh = $this->userRepository->storeRefreshToken('username', $user->username, bin2hex(random_bytes(16)));

            $newAccessToken = [
                'iss' => url()->current(),
                'iat' => now()->timestamp,
                'nbf' => now()->timestamp,
                'exp' => $newTokenAccess->access_token_expired_at->timestamp,
                'jti' => bin2hex(random_bytes(8)),
                'sub' => $newTokenAccess->id,
                'username' => $newTokenAccess->username,
                'access_token' => $newTokenAccess->access_token,
            ];
            $newAccessTokenRefresh = [
                'refresh_token' => $newTokenRefresh->refresh_token,
                'username' => $newTokenRefresh->username,
                'iat' => time(),
                'exp' => $newTokenRefresh->refresh_token_expired_at->timestamp,
            ];

            $access = JWT::encode($newAccessToken, $secret, 'HS256');
            $refresh = JWT::encode($newAccessTokenRefresh, $secret, 'HS256');

            // Set secure cookies
            return ResponseJsonFormater::success(
                message: 'Access token refreshed successfully'
            )->withCookie(cookie('access_token', $access, 60, '/', null, true, true))
                ->withCookie(cookie('refresh_token', $refresh, 60 * 24 * 7, '/', null, true, true));
        } catch (\UnexpectedValueException $e) {
            return ResponseJsonFormater::error(
                code: 401,
                message: 'Invalid token: ' . $e->getMessage()
            );
        } catch (\Exception $e) {
            return ResponseJsonFormater::error(
                code: 500,
                message: 'Failed to refresh token: ' . $e->getMessage()
            );
        }
    }
}
