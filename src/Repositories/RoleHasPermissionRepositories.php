<?php

namespace Ijp\Auth\Repositories;


class RoleHasPermissionRepositories
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function getAllRoleHasPermission()
    {
        return $this->model->all();
    }

    public function getRoleHasPermissionById($id)
    {
        return $this->model->find($id);
    }
    public function getRoleHasPermissionByRoleId($roleId)
    {
        return $this->model->where('role_id', $roleId)->get();
    }

    public function stotreRoleHasPermission($data)
    {
        $permissionid = $data->permission_ids;
        foreach ($permissionid as $key => $value) {
            // check if permission id is already exist
            $permission = $this->model->where('permission_id', $value)->where('role_id', $data->role_id)->first();
            if ($permission) {
                continue;
            }
            $this->model::create([
                'role_id' => $data->role_id,
                'permission_id' => $value,
            ]);
        }
        return $this->model->where('role_id', $data->role_id)->get();
    }

    public function updateRoleHasPermission($data)
    {
        $roleId = $data->role_id;

        if (empty($roleId)) {
            throw new \Exception('Role ID is missing or null');
        }

        // Cari role berdasarkan role_id
        $role = $this->model->where('role_id', $roleId)->first();

        if (!$role) {
            throw new \Exception('Role not found');
        }

        // Debug untuk memastikan $role valid
        if (!($role instanceof \Ijp\Auth\Model\RoleHasPermissionModel)) {
            throw new \Exception('Invalid role instance');
        }

        // Sinkronisasi permissions pada instance role
        $role->permissions()->sync($data->permission_ids);

        return $role->permissions;
    }
}
