<?php

namespace Fahmiardi\Mongodb\Permissions\Models;

use Moloquent\Eloquent\Model;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class Permission extends Model implements PermissionContract
{
    /**
     * A permission can be applied to roles.
     *
     * @return \Illuminate\Support\Collection $roles
     */
    public function roles()
    {
        return $this->getPermissions(
            config('laravel-permission.models.role')
        );
    }

    /**
     * A role may be assigned to various users.
     *
     * @return \Illuminate\Support\Collection $users
     */
    public function users()
    {
        return $this->getPermissions(
            config('auth.model') ?: config('auth.providers.users.model')
        );
    }

    /**
     * Find a permission by its name.
     *
     * @param string $name
     *
     * @throws PermissionDoesNotExist
     */
    public static function findByName($name)
    {
        $permission = static::where('name', $name)->first();

        if (! $permission) {
            throw new PermissionDoesNotExist();
        }

        return $permission;
    }

    protected function getPermissions($model)
    {
        return (new $model)->where('permissions.id', $this->getAttribute($this->primaryKey));
    }
}
