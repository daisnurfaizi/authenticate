<?php

namespace Ijp\Auth\Model;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Guid\Guid;

class RoleHasPermissionModel extends Model
{
    protected $table = 'app_role_has_permissions';
    protected $keyType = 'string';
    public $incrementing = false; // Pastikan non-incremental karena menggunakan UUID
    protected $fillable = [
        'role_id',
        'permission_id',
    ];

    public function role()
    {
        return $this->belongsTo(RoleModel::class, 'role_id', 'id');
    }

    public function permission()
    {
        return $this->belongsTo(PermissionModel::class, 'permission_id', 'id');
    }

    public function permissions()
    {
        return $this->belongsToMany(
            PermissionModel::class,
            'app_role_has_permissions',
            'role_id',
            'permission_id'
        )
            ->withTimestamps();
    }
}
