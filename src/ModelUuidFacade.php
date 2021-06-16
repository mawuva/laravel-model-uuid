<?php

namespace Mawuekom\ModelUuid;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mawuekom\ModelUuid\Skeleton\SkeletonClass
 */
class ModelUuidFacade extends Facade
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
