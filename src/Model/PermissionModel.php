<?php

namespace Ijp\Auth\Model;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Guid\Guid;

class PermissionModel extends Model
{
    protected $table = 'app_permissions';
    protected $keyType = 'string';
    protected $fillable = [
        'name'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Guid::uuid4();
            }
        });
    }

    public function roles()
    {
        return $this->belongsToMany(RoleModel::class, 'role_has_permissions', 'permission_id', 'role_id');
    }
}
