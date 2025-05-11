<?php

namespace Ijp\Auth\Repositories;

use Illuminate\Database\Eloquent\Model;

class UserRepository extends BaseRepositories
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    public function storeRefreshToken(string $field, string $value, string $token)
    {
        $userStored = $this->showBy(field: $field, value: $value);
        if ($userStored) {
            $userStored->refresh_token = $token;
            $userStored->last_login = now();
            $userStored->save();
            return $userStored;
        }
        throw new \Exception('User not found');
    }


    public function validateRefreshToken(string $field, string $value, string $token)
    {
        $userStored = $this->showBy(field: $field, value: $value);
        if ($userStored->refresh_token == $token) {
            return true;
        }
        return false;
    }

    public function revokeRefreshToken($field, $value)
    {
        $userStored = $this->showBy(field: $field, value: $value);
        if ($userStored) {
            $userStored->refresh_token = null;
            $userStored->save();
            return true;
        }
        return throw new \Exception('User not found');
    }



    public function updateUser($user, $data)
    {
        $userStored = $this->showBy(field: 'id', value: $user->id);
        $userStored->update($data);
        return $userStored;
    }

    public function updateRole($user, $role)
    {
        $userStored = $this->showBy(field: 'id', value: $user->id);
        $userStored->role_id = $role;
        $userStored->save();
        return $userStored;
    }

    public function updateStatus($user, $status)
    {
        $userStored = $this->showBy(field: 'id', value: $user->id);
        $userStored->status = $status;
        $userStored->save();
        return $userStored;
    }
}
