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
            $userStored->refresh_token_expired_at = now()->addDays(7);
            $userStored->last_login = now();
            $userStored->save();
            return $userStored;
        }
        throw new \Exception('User not found');
    }

    public function storeAccessToken(string $field, string $value, string $token)
    {
        $userStored = $this->showBy(field: $field, value: $value);
        if ($userStored) {
            $userStored->access_token = $token;
            $userStored->access_token_expired_at = now()->addMinutes(60);
            $userStored->save();
            return $userStored;
        }
        throw new \Exception('User not found');
    }


    public function validateRefreshToken(string $field, string $value, string $token)
    {
        $userStored = $this->showBy(field: $field, value: $value);
        //    validate token expired and token == $token
        if ($userStored->refresh_token == $token && $userStored->refresh_token_expired_at > now()) {
            return true;
        }
        return false;
    }

    public function revokeRefreshToken($field, $value)
    {
        $userStored = $this->showBy(field: $field, value: $value);
        if ($userStored) {
            $userStored->refresh_token = null;
            $userStored->refresh_token_expired_at = null;
            $userStored->save();
            return true;
        }
        return throw new \Exception('User not found');
    }

    public function revokeAccessToken($field, $value)
    {
        $userStored = $this->showBy(field: $field, value: $value);
        if ($userStored) {
            $userStored->access_token = null;
            $userStored->access_token_expired_at = null;
            $userStored->save();
            return true;
        }
        return throw new \Exception('User not found');
    }



    public function updateUser($user, $data)
    {
        $userStored = $this->showBy(field: 'id', value: $user);
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

    public function getUserById($id)
    {
        $user = $this->showBy(field: 'id', value: $id);
        if ($user) {
            return $user;
        }
        return throw new \Exception('User not found');
    }

    public function getAllUser($columns = ['*'], $paginate = false, $perPage = 15)
    {
        $query = $this->getModels()::select($columns);

        if ($paginate) {
            return $query->paginate($perPage)->appends(request()->query());
        }

        return $query->get();
    }

    public function validateAccessToken(string $field, string $value, string $token)
    {
        $userStored = $this->showBy(field: $field, value: $value);
        //    validate token expired and token == $token
        if ($userStored->access_token == $token && $userStored->access_token_expired_at > now()) {
            return true;
        }
        return false;
    }

    public function deleteUser($id)
    {
        return $this->delete($id);
    }

    public function createUser($data)
    {
        $user = $this->create($data);
        return $user;
    }
}
