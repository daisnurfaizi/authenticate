<?php

namespace App\Http\Controllers\Permissions;

use App\Http\Controllers\Controller;
use Ijp\Auth\Model\PermissionModel;
use Ijp\Auth\Repositories\PermissionRepositories;
use Ijp\Auth\Service\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected $permissionService;
    public function __construct()
    {
        $this->permissionService = new PermissionService(new PermissionRepositories(new PermissionModel()));
    }

    public function store(Request $request)
    {
        // Validate the request data
        return $this->permissionService->storePermission($request);
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        // return $this->permissionService->updatePermission($request, $id);
    }

    public function showAllPermissions(Request $request)
    {
        return $this->permissionService->getAllPermissions($request);
    }

    public function showPermissionByRoleID($id)
    {
        // Validate the request data
        return $this->permissionService->getPermissionByRole($id);
    }

    public function showPermissionByID($id)
    {
        // Validate the request data
        return $this->permissionService->getPermissionById($id);
    }
    public function delete($id)
    {
        // Validate the request data
        return $this->permissionService->deletePermission($id);
    }

    public function updatePermission(Request $request, $id)
    {
        // Validate the request data
        return $this->permissionService->updatePermission($request, $id);
    }
}
