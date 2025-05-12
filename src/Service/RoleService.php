<?php

namespace Ijp\Auth\Service;

use Ijp\Auth\Model\RoleModel;
use Ijp\Auth\Repositories\RoleRepositories;

class RoleService
{
    protected $roleRepositories;
    // protected $permissionRepositories;

    public function __construct()
    {
        $this->roleRepositories = new RoleRepositories(new RoleModel());
        // $this->permissionRepositories = $permissionRepositories;
    }

    public function storeRole($data)
    {
        return $this->roleRepositories->storeRole($data);
    }

    public function getRoleById($id)
    {
        return $this->roleRepositories->getRoleById($id);
    }
}
