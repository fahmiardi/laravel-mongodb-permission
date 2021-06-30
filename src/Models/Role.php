<?php

namespace Fahmiardi\Mongodb\Permissions\Models;

use Moloquent\Eloquent\Model;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class Role extends Model implements RoleContract
{
    use HasPermissions;

    /**
     * A role may be given various permissions.
     *
     * @return \Moloquent\Eloquent\Relations\EmbedsMany
     */
    public function permissions() : \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->embedsMany(
            config('laravel-permission.table_names.role_has_permissions')
        );
    }

    /**
     * A role may be assigned to various users.
     *
     * @return \Illuminate\Support\Collection $users
     */
    public function users()
    {
        return $this->getUsers(
            config('auth.model') ?: config('auth.providers.users.model')
        );
    }

    /**
     * Find a role by its name.
     *
     * @param string $name
     *
     * @throws RoleDoesNotExist
     *
     * @return Role
     */
    public static function findByName($name)
    {
        $role = static::where('name', $name)->first();

        if (! $role) {
            throw new RoleDoesNotExist();
        }

        return $role;
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     */
    public function hasPermissionTo($permission): bool
    {
        if (is_string($permission)) {
            $permission = app(Permission::class)->findByName($permission);
        }

        return $this->permissions->contains('id', $permission->_id);
    }

    protected function getUsers($model)
    {
        return (new $model)->where('roles.id', $this->getAttribute($this->primaryKey));
    }
}
