<?php

namespace Moonshiner\SafeQueuing;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Moonshiner\SafeQueuing\Skeleton\SkeletonClass
 */
class SafeQueuingFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'safe-queuing';
    }
}
