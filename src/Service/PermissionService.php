<?php

namespace Ijp\Auth\Service;

use App\Helper\ResponseJsonFormater;
use Ijp\Auth\Model\PermissionModel;
use Ijp\Auth\Repositories\PermissionRepositories;
use Ijp\Auth\Traits\PaginateResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PermissionService
{
    use PaginateResolver;
    protected $permissionRepositories;

    public function __construct()
    {
        $this->permissionRepositories = new PermissionRepositories(new PermissionModel());
    }

    public function storePermission($data)
    {
        try {
            DB::beginTransaction();
            // Validate the request data
            $this->validatePermission($data);
            $permissionStored = $this->permissionRepositories->storePermission($data->all());
            DB::commit();
            return ResponseJsonFormater::success(
                message: 'Permission stored successfully',
                data: [
                    'id' => $permissionStored->id,
                    'name' => $permissionStored->name,
                ]
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
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
                message: 'Failed to store permission',
            );
        }
    }

    private function validatePermission($data)
    {
        return Validator::make($data->all(), [
            'name' => 'required|string|max:255',
        ]);
    }

    public function getAllPermissions($request)
    {
        try {
            // $perPage = (int) $request->query('perPage', 15); // Default 15 jika tidak ada perPage
            // $paginate = $request->query('paginate', false); // Tangkap nilai paginate

            // Konversi `paginate` menjadi boolean (jika hanya kunci tanpa nilai, anggap true)
            // $paginate = filter_var($paginate, FILTER_VALIDATE_BOOLEAN) || $request->has('paginate');
            $paginateResolver = $this->resolvePagination($request);
            $paginate = filter_var($paginateResolver['paginate'], FILTER_VALIDATE_BOOLEAN) || $request->has('paginate');
            $perPage = (int) $paginateResolver['perPage']; // Default 15 jika tidak ada perPage

            // Ambil data permissions dengan kondisi yang sesuai
            $permissions = $this->permissionRepositories->showAllPermission(
                columns: ['id', 'name'],
                paginate: $paginate,
                perPage: $perPage
            );

            return ResponseJsonFormater::success(
                message: 'Permissions retrieved successfully',
                data: $permissions,
            );
        } catch (\Exception $e) {
            return ResponseJsonFormater::error(
                code: 500,
                message: 'Failed to retrieve permissions',
            );
        }
    }



    public function getAllPermisionPage()
    {
        try {
            $permissions = $this->permissionRepositories->showAllPermission(columns: ['id', 'name'])->paginate(10);
            return ResponseJsonFormater::success(
                message: 'Permissions retrieved successfully',
                data: $permissions,
            );
        } catch (\Exception $e) {
            return ResponseJsonFormater::error(
                code: 500,
                message: 'Failed to retrieve permissions',
            );
        }
    }

    public function updatePermission($data, $id)
    {
        try {
            DB::beginTransaction();
            // Validate the request data
            $this->validatePermission($data);
            $permissionStored = $this->permissionRepositories->updatePermission($id, $data->all());
            DB::commit();
            return ResponseJsonFormater::success(
                message: 'Permission updated successfully',
                data: [
                    'id' => $permissionStored->id,
                    'name' => $permissionStored->name,
                ]
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
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
                message: 'Failed to update permission',
            );
        }
    }
    public function deletePermission($id)
    {
        try {
            DB::beginTransaction();
            $this->permissionRepositories->deletePermission($id);
            DB::commit();
            return ResponseJsonFormater::success(
                message: 'Permission deleted successfully',
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
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
                message: 'Failed to delete permission',
            );
        }
    }

    public function getPermissionByRole($roleId)
    {
        try {
            $permissions = $this->permissionRepositories->getPermissionByRole($roleId);
            return ResponseJsonFormater::success(
                message: 'Permissions retrieved successfully',
                data: $permissions,
            );
        } catch (\Exception $e) {
            return ResponseJsonFormater::error(
                code: 500,
                message: 'Failed to retrieve permissions',
            );
        }
    }

    public function getPermissionById($id)
    {
        try {
            $permissionStored = $this->permissionRepositories->showPermission($id);
            return ResponseJsonFormater::success(
                message: 'Permission retrieved successfully',
                data: $permissionStored,
            );
        } catch (\Exception $e) {
            return ResponseJsonFormater::error(
                code: 500,
                message: 'Failed to retrieve permission',
            );
        }
    }
}
