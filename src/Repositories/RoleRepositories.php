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
        $roleStored = $this->showBy(field: 'id', value: $id);
        return $roleStored;
    }
}
