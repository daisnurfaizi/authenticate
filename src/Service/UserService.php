<?php

namespace Ijp\Auth\Service;

use App\Helper\ResponseJsonFormater;
use Ijp\Auth\Traits\PaginateResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService
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
                    message: 'username or password is incorrect'
                );
            }


            $user = Auth::user();
            $permissions = $user->role && $user->role->permissions
                ? $user->role->permissions->map(function ($roleHasPermission) {
                    return [
                        'id' => $roleHasPermission->permission && $roleHasPermission->permission->id ? $roleHasPermission->permission->id : null,
                        'name' => $roleHasPermission->permission && $roleHasPermission->permission->name ? $roleHasPermission->permission->name : null,
                    ];
                })
                : [];

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
                ->withCookie('access_token', $this->createToken([
                    'username' => $request->username,
                    'role' => $user->role ? [
                        'id' => $user->role->id ?? null,
                        'name' => $user->role->name ?? null,
                    ] : null,
                    'permissions' => $permissions,
                ]), 60)
                ->withCookie('refresh_token', $this->createRefreshToken($request), 60 * 24 * 30);
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
     * Create a refresh token for the user.
     *
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function createRefreshToken(Request $request)
    {
        try {
            DB::beginTransaction();

            $token = bin2hex(random_bytes(16));
            $createdToken = $this->userRepository->storeRefreshToken('username', $request->username, $token);

            if (!$createdToken) {
                throw new \Exception('Failed to create refresh token', 404);
            }

            DB::commit();

            return $this->createToken([
                'refresh_token' => $token,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create refresh token', 500);
        }
    }


    /**
     * Create a JWT token with custom payload.
     *
     * @param array $customPayload
     * @return string
     */
    public function createToken(array $customPayload = [])
    {
        // Create payload for token
        $payload = JWTAuth::factory()->customClaims($customPayload)->make();

        // Create token
        return JWTAuth::fromUser(Auth::user(), $payload);
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
    // update user

    public function updateUser($data, $id)
    {
        // dd($id);
        try {
            DB::beginTransaction();
            $user = $this->userRepository->getUserById($id);
            if (!$user) {
                return ResponseJsonFormater::error(
                    code: 404,
                    message: 'User not found',
                );
            }
            $userStored = $this->userRepository->updateUser($id, $data->all());
            DB::commit();
            return ResponseJsonFormater::success(
                message: 'User updated successfully',
                data: [
                    'id' => $userStored->id,
                    'username' => $userStored->username,
                ]
            );
        } catch (ValidationException $e) {
            return ResponseJsonFormater::error(
                code: 422,
                message: 'Validation error',
                data: $e->errors(),
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseJsonFormater::error(
                code: 500,
                message: $e->getMessage(),
            );
        }
    }
    public function deleteUser($id)
    {
        try {
            DB::beginTransaction();
            $this->userRepository->deleteUser($id);
            DB::commit();
            return ResponseJsonFormater::success(
                message: 'User deleted successfully',
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseJsonFormater::error(
                code: 500,
                message: 'Failed to delete user',
            );
        }
    }

    public function getUserById($id)
    {
        try {
            $user = $this->userRepository->getUserById($id);
            if (!$user) {
                return ResponseJsonFormater::error(
                    code: 404,
                    message: 'User not found',
                );
            }
            return ResponseJsonFormater::success(
                message: 'User retrieved successfully',
                data: [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role ? [
                        'id' => $user->role->id ?? null,
                        'name' => $user->role->name ?? null,
                    ] : null,
                    'permissions' => $user->role && $user->role->permissions
                        ? $user->role->permissions->map(function ($roleHasPermission) {
                            return [
                                'id' => $roleHasPermission->permissions && $roleHasPermission->permissions->id ? $roleHasPermission->permissions->id : null,
                                'name' => $roleHasPermission->permissions && $roleHasPermission->permissions->name ? $roleHasPermission->permissions->name : null,
                            ];
                        })
                        : [],
                ]
            );
        } catch (\Exception $e) {
            return ResponseJsonFormater::error(
                code: 500,
                message: 'Failed to retrieve user',
            );
        }
    }

    public function getAllUser($request)
    {
        try {
            $paginateResolver = $this->resolvePagination($request);
            $users = $this->userRepository->getAllUser(
                columns: ['id', 'username', 'role_id'],
                paginate: $paginateResolver['paginate'],
                perPage: $paginateResolver['perPage']
            );
            return ResponseJsonFormater::success(
                message: 'Users retrieved successfully',
                data: $users,
            );
        } catch (\Exception $e) {
            return ResponseJsonFormater::error(
                code: 500,
                message: 'Failed to retrieve users',
            );
        }
    }
}
