<?php

namespace Ijp\Auth\Repositories;

use Illuminate\Database\Eloquent\Model;


class RoleRepositories extends BaseRepositories
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    public function storeRole($data)
    {
        $roleStored = $this->model->create($data);
        return $roleStored;
    }

    public function getRoleById($id)
    {
        $roleStored = $this->getModels()::select('id', 'name', 'description', 'status') // 'name' harus ada di sini
            ->with('permissions') // Relasi 'permissions' dengan kolom yang diinginkan
            ->where('id', $id)
            ->first();
        return $roleStored;
    }

    public function updateRole($id, $data)
    {
        $roleStored = $this->showBy(field: 'id', value: $id);
        if (!$roleStored) {
            throw new \Exception('Role not found');
        }
        $roleStored->update($data);
        return $roleStored;
    }

    public function deleteRole($id)
    {
        $roleStored = $this->showBy(field: 'id', value: $id);
        if ($roleStored) {
            $roleStored->delete();
            return $roleStored;
        }
        return false;
    }

    public function showAllRole($columns = ['*'], $paginate = false, $perPage = 15)
    {
        $query = $this->getModels()::select($columns);

        if ($paginate) {
            return $query->paginate($perPage)->appends(request()->query());
        }

        return $query->get();
    }

    public function updateRoleHasPermission($data)
    {
        // Ambil role_id dari data
        $roleId = $data->role_id;

        // Validasi role_id
        if (empty($roleId)) {
            throw new \Exception('Role ID is missing or null');
        }

        // Cari role berdasarkan role_id
        $role = \Ijp\Auth\Model\RoleModel::find($roleId);

        // Debug untuk memastikan $role ditemukan
        if (!$role) {
            throw new \Exception('Role not found');
        }

        // Validasi bahwa $role adalah instance dari RoleModel
        if (!($role instanceof \Ijp\Auth\Model\RoleModel)) {
            throw new \Exception('Invalid role instance');
        }

        // Sinkronisasi permissions pada instance role
        $role->permission()->sync($data->permission_ids);

        // Kembalikan data permissions yang terhubung ke role
        return $role->permission()->get();
    }
}
