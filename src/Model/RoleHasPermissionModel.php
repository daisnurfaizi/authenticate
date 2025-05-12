<?php

namespace Ijp\Auth\Model;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Guid\Guid;

class RoleHasPermissionModel extends Model
{
    protected $table = 'app_role_has_permissions';
    protected $keyType = 'string';
    protected $fillable = [
        'role_id',
        'permission_id',
        'name',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Guid::uuid4();
            }
        });
    }
    public function role()
    {
        return $this->belongsTo(RoleModel::class, 'role_id', 'id');
    }

    public function permission()
    {
        return $this->belongsTo(PermissionModel::class, 'permission_id', 'id');
    }
}
