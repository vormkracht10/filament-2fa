<?php

namespace Vormkracht10\TwoFactorAuth\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vormkracht10\TwoFactorAuth\TwoFactorAuth
 */
class TwoFactorAuth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Vormkracht10\TwoFactorAuth\TwoFactorAuth::class;
    }
}
