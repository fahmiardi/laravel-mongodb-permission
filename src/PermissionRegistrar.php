<?php

namespace Fahmiardi\Mongodb\Permissions;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Cache\CacheManager;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\PermissionRegistrar as BasePermissionRegistrar;

class PermissionRegistrar extends BasePermissionRegistrar
{
    /**
     * {@inheritdoc}
     */
    public function __construct(CacheManager $cacheManager)
    {
        parent::__construct($cacheManager);
    }

    /**
     * Get the current permissions.
     *
     * @return \Moloquent\Eloquent\Collection
     */
    public function getPermissions(array $params = Array()) : \Illuminate\Database\Eloquent\Collection
    {
        return app(Permission::class)->get();
    }
}
