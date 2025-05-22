<?php

namespace Ijp\Auth\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Guid\Guid;

class RoleModel extends Model
{
    public $incrementing = false;
    protected $table = 'app_role';
    protected $keyType = 'string';
    protected $fillable = [
        'name',
        'description',
        'status',

    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Guid::uuid4();
            }
        });
    }

    public function permissions()
    {
        return $this->hasMany(RoleHasPermissionModel::class, 'role_id', 'id');
    }

    public function permission()
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
