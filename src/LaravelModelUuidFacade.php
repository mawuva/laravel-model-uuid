<?php

namespace Mawuekom\LaravelModelUuid;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mawuekom\LaravelModelUuid\Skeleton\SkeletonClass
 */
class LaravelModelUuidFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-model-uuid';
    }
}
