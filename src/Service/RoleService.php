<?php

namespace Ijp\Auth\Service;

use App\Helper\ResponseJsonFormater;
use Ijp\Auth\Model\RoleHasPermissionModel;
use Ijp\Auth\Model\RoleModel;
use Ijp\Auth\Repositories\RoleHasPermissionRepositories;
use Ijp\Auth\Repositories\RoleRepositories;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RoleService
{
    protected $roleRepositories;
    protected $permissionRepositories;

    public function __construct()
    {
        $this->roleRepositories = new RoleRepositories(new RoleModel());
        $this->permissionRepositories = new RoleHasPermissionRepositories(new RoleHasPermissionModel());
    }

    public function storeRole($data)
    {
        try {
            // Validate the request data
            $this->validateRole($data);
            DB::beginTransaction();
            $roleStored = $this->roleRepositories->storeRole($data->all());
            DB::commit();
            return ResponseJsonFormater::success(
                message: 'Role stored successfully',
                data: [
                    'id' => $roleStored->id,
                    'name' => $roleStored->name,
                ]
            );
        } catch (ValidationException $e) {
            return ResponseJsonFormater::error(
                code: 422,
                message: 'Validation error',
                data: $e->errors(),
            );
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            $errorCode = $e->errorInfo[1] ?? null;
            if ($errorCode == 1062) { // MySQL duplicate entry error code
                return ResponseJsonFormater::error(
                    code: 409,
                    message: 'Role with this name already exists',
                );
            }
            return ResponseJsonFormater::error(
                code: 500,
                message: 'Failed to store role',
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseJsonFormater::error(
                code: 500,
                message: 'Failed to store role',
            );
        }
    }

    public function updateRole($data, $id)
    {
        try {
            // Validate the request data
            $this->validateRole($data);
            DB::beginTransaction();
            $roleStored = $this->roleRepositories->updateRole($id, $data->all());
            DB::commit();
            return ResponseJsonFormater::success(
                message: 'Role updated successfully',
                data: [
                    'id' => $roleStored->id,
                    'name' => $roleStored->name,
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
                message: 'Failed to update role' . $e->getMessage(),
            );
        }
    }

    public function deleteRole($id)
    {
        try {
            DB::beginTransaction();
            $roleStored = $this->roleRepositories->deleteRole($id);
            DB::commit();
            return ResponseJsonFormater::success(
                message: 'Role deleted successfully',
                data: [
                    'id' => $roleStored->id,
                    'name' => $roleStored->name,
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
                message: 'Failed to delete role',
            );
        }
    }


    public function validateRole($data)
    {
        // Validate the request data
        $data = Validator::make($data->all(), [
            'id' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        if ($data->fails()) {
            throw new ValidationException($data);
        }
    }

    public function getRoleById($id)
    {
        $role = $this->roleRepositories->getRoleById($id);
        if ($role) {
            return ResponseJsonFormater::success(
                message: 'Role retrieved successfully',
                data: $role

            );
        } else {
            return ResponseJsonFormater::error(
                code: 404,
                message: 'Role not found',
            );
        }
    }

    public function getRoles($perPage = 10)
    {
        $roles = $this->roleRepositories->getModels()::select('id', 'name', 'description', 'status')
            ->paginate($perPage);

        return ResponseJsonFormater::success(
            message: 'Roles retrieved successfully',
            data: $roles,
        );
    }

    public function addRolePermissions($data)
    {
        try {
            DB::beginTransaction();

            $roleHasPermission = $this->permissionRepositories->stotreRoleHasPermission($data);

            if (!$roleHasPermission) {
                return ResponseJsonFormater::error(
                    code: 404,
                    message: 'Role permissions not found',
                );
            }
            DB::commit();
            return ResponseJsonFormater::success(
                message: 'Role permissions updated successfully',
                data: $roleHasPermission,
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
                message: 'Failed to update role permissions' . $e->getMessage(),
            );
        }
    }

    public function updateRolePermissions($data)
    {
        try {
            DB::beginTransaction();

            $roleHasPermission = $this->roleRepositories->updateRoleHasPermission($data);

            if (!$roleHasPermission) {
                return ResponseJsonFormater::error(
                    code: 404,
                    message: 'Role permissions not found',
                );
            }
            DB::commit();
            return ResponseJsonFormater::success(
                message: 'Role permissions updated successfully',
                data: $roleHasPermission,
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
                message: 'Failed to update role permissions' . $e->getMessage(),
            );
        }
    }
}
