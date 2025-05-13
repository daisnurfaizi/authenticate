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
}
