<?php

namespace Ijp\Auth\Repositories;

use Illuminate\Database\Eloquent\Model;

class PermissionRepositories extends BaseRepositories
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    public function storePermission($data)
    {
        return $this->create($data);
    }

    public function updatePermission($id, $data)
    {
        $permissionStored = $this->showBy(field: 'id', value: $id);
        if ($permissionStored) {
            $permissionStored->update($data);
            return $permissionStored;
        }
        throw new \Exception('Permission not found');
    }

    public function deletePermission($id)
    {
        return $this->delete($id);
    }

    public function showPermission($id)
    {
        $permissionStored = $this->showBy(field: 'id', value: $id);
        if ($permissionStored) {
            return $permissionStored;
        }
        throw new \Exception('Permission not found');
    }
    public function showAllPermission($columns = ['*'], $paginate = false, $perPage = 15)
    {
        $query = $this->getModels()::select($columns);

        if ($paginate) {
            return $query->paginate($perPage)->appends(request()->query());
        }

        return $query->get();
    }

    public function getPermissionByRole(string $roleId)
    {
        return $this->getModels()::select(['id', 'name'])->whereHas('roles', function ($query) use ($roleId) {
            $query->where('role_id', $roleId);
        })->get();
    }
}
