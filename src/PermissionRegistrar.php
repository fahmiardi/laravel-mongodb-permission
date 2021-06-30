<?php

namespace Fahmiardi\Mongodb\Permissions;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Auth\Access\Gate;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\PermissionRegistrar as BasePermissionRegistrar;

class PermissionRegistrar extends BasePermissionRegistrar
{
    /**
     * {@inheritdoc}
     */
    public function __construct(Gate $gate, Repository $cache)
    {
        parent::__construct($gate, $cache);
    }

    /**
     * Get the current permissions.
     *
     * @return \Moloquent\Eloquent\Collection
     */
    public function getPermissions()
    {
        return app(Permission::class)->get();
    }
}
