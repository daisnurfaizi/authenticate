<?php

namespace Ijp\Auth\Service;

use App\Helper\ResponseJsonFormater;
use Ijp\Auth\Traits\PaginateResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class UserService
{
    use PaginateResolver;
    protected $userRepository;

    public function __construct($userRepository)
    {
        $this->userRepository = $userRepository;
    }


    // update user

    public function updateUser($data, $id)
    {
        // dd($id);
        try {
            DB::beginTransaction();
            // handle photo upload if exists
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
    public function registerUser(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = Validator::make(
                $request->all(),
                [
                    'username' => 'required|string|max:255|unique:users,username',
                    'password' => 'required|string|min:8|confirmed',
                    'role_id' => 'required|exists:app_role,id',
                    'name' => 'nullable|string|max:30',
                    'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                ],
                [
                    'username.required' => 'Username is required',
                    'username.unique' => 'Username already exists',
                    'password.required' => 'Password is required',
                    'password.min' => 'Password must be at least 8 characters',
                    'password.confirmed' => 'Password confirmation does not match',
                    'role_id.required' => 'Role ID is required',
                    'role_id.exists' => 'Role ID does not exist',
                ]
            )->validate();

            $data['password'] = bcrypt($data['password']);
            $user = $this->userRepository->createUser($data);

            DB::commit();
            return ResponseJsonFormater::success(
                message: 'User registered successfully',
                data: [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role_id' => $user->role_id,
                ]
            );
        } catch (ValidationException $e) {
            DB::rollBack();
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

    /**
     * Role user
     */

    public function updateRoleUser($request, $id)
    {

        try {

            DB::beginTransaction();
            $data = Validator::make(
                $request->all(),
                [

                    'role_id' => 'required|exists:app_role,id',
                ],
                [

                    'role_id.required' => 'Role ID is required',
                    'role_id.exists' => 'Role ID does not exist',
                ]
            )->validate();


            $user = $this->userRepository->getUserById($id);
            if (!$user) {
                return ResponseJsonFormater::error(
                    code: 404,
                    message: 'User not found',
                );
            }

            $userStored = $this->userRepository->updateRole($user, $data['role_id']);
            DB::commit();
            return ResponseJsonFormater::success(
                message: 'Role updated successfully',
                data: [
                    'id' => $userStored->id,
                    'username' => $userStored->username,
                    'role_id' => $userStored->role_id,
                ]
            );
        } catch (ValidationException $e) {
            DB::rollBack();
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
}
