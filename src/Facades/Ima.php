<?php

namespace Davitig\Ima\Facades;

use Davitig\Ima\Ima as BaseIma;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Davitig\Ima\Ima
 */
class Ima extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BaseIma::class;
    }
}
