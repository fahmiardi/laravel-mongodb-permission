<?php

namespace Fahmiardi\Mongodb\Permissions\Traits;

use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Fahmiardi\Mongodb\Permissions\Contracts\EmbedRole;
use Carbon\Carbon;

trait HasRoles
{
    use HasPermissions;

    /**
     * A user may have multiple roles.
     *
     * @return \Moloquent\Eloquent\Relations\EmbedsMany
     */
    public function roles()
    {
        return $this->embedsMany(
            config('laravel-permission.table_names.role_has_permissions')
        );
    }

    /**
     * A user may have multiple direct permissions.
     *
     * @return \Moloquent\Eloquent\Relations\EmbedsMany
     */
    public function permissions()
    {
        return $this->embedsMany(
            config('laravel-permission.table_names.user_has_permissions')
        );
    }

    /**
     * Assign the given role to the user.
     *
     * @param array|string|\Fahmiardi\Mongodb\Permissions\Models\Role ...$roles
     *
     * @return \Fahmiardi\Mongodb\Permissions\Contracts\Role
     */
    public function assignRole(...$roles)
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                return $this->getStoredRole($role);
            })
            ->all();

        foreach ($roles as $role) {
            if (! $this->roles()->where('id', $role->_id)->first()) {
                $this->roles()->associate(app(EmbedRole::class)->forceFill([
                    'id' => $role->_id,
                    'created_at' => Carbon::now()
                ]));
            }
        }

        $this->save();

        return $this;
    }

    /**
     * Revoke the given role from the user.
     *
     * @param string|Role $role
     */
    public function removeRole($role)
    {
        $role = $this->getStoredRole($role);
        $embedRole = $this->roles()->where('id', $role->_id);

        $this->roles()->detach($embedRole);

        return $this;
    }

    /**
     * Determine if the user has (one of) the given role(s).
     *
     * @param string|array|Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasRole($roles)
    {
        if (is_string($roles)) {
            $roles = $this->getStoredRole($roles);

            return $this->roles->contains('id', $roles->_id);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->_id);
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }

            return false;
        }

        return (bool) $roles->intersect($this->roles)->count();
    }

    /**
     * Determine if the user has any of the given role(s).
     *
     * @param string|array|Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasAnyRole($roles)
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if the user has all of the given role(s).
     *
     * @param string|Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasAllRoles($roles)
    {
        if (is_string($roles)) {
            $roles = $this->getStoredRole($roles);

            return $this->roles->contains('id', $roles->_id);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->_id);
        }

        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->_id : $role;
        });

        return $roles->intersect($this->roles->pluck('id')) == $roles;
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     */
    public function hasPermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = $this->getStoredPermission($permission);
        }

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    /**
     * Determine if the user has, via roles, the given permission.
     *
     * @param Permission $permission
     *
     * @return bool
     */
    protected function hasPermissionViaRole(Permission $permission)
    {
        $roles = app(Role::class)->whereIn('_id', $this->roles->pluck('id'))->get();

        foreach ($roles as $role) {
            if ($role->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the user has the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     */
    protected function hasDirectPermission($permission)
    {
        if (is_string($permission)) {
            $permission = app(Permission::class)->findByName($permission);

            if (! $permission) {
                return false;
            }
        }

        return $this->permissions->contains('id', $permission->_id);
    }

    /**
     * Remove all current roles and set the given ones.
     *
     * @param array ...$roles
     *
     * @return $this
     */
    public function syncRoles(...$roles)
    {
        $this->roles()->delete();

        return $this->assignRole($roles);
    }

    /**
     * @param $role
     *
     * @return Role
     */
    protected function getStoredRole($role)
    {
        if (is_string($role)) {
            return app(Role::class)->findByName($role);
        }

        return $role;
    }
}
