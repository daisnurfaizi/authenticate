<?php

namespace Ijp\Auth\Model;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Guid\Guid;

class PermissionModel extends Model
{
    protected $table = 'app_permissions';
    protected $keyType = 'string';
    public $incrementing = false;
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
        return $this->belongsToMany(
            RoleModel::class,            // Model Role
            'app_role_has_permissions', // Tabel pivot
            'permission_id',            // FK di tabel pivot ke Permission
            'role_id'                   // FK di tabel pivot ke Role
        )->withTimestamps();
    }
}
