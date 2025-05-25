<?php

namespace App\Http\Controllers;

use Ijp\Auth\Model\RoleModel;
use Ijp\Auth\Repositories\RoleRepositories;
use Ijp\Auth\Service\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $roleService;
    public function __construct()
    {
        $this->roleService = new RoleService(new RoleRepositories(new RoleModel()));
    }
    public function store(Request $request)
    {
        // Validate the request data
        return $this->roleService->storeRole($request);
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        return $this->roleService->updateRole($request, $id);
    }

    public function delete($id)
    {
        // Validate the request data
        return $this->roleService->deleteRole($id);
    }

    public function show()
    {
        // Validate the request data
        return $this->roleService->getRoles();
    }

    public function showById($id)
    {
        // Validate the request data
        return $this->roleService->getRoleById($id);
    }

    public function addPermissionToRole(Request $request)
    {
        // Validate the request data
        return $this->roleService->addRolePermissions(data: $request);
    }

    public function updateRolePermission(Request $request)
    {
        // Validate the request data
        return $this->roleService->updateRolePermissions(data: $request);
    }
}
