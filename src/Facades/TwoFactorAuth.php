<?php

namespace Backstage\TwoFactorAuth\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Backstage\TwoFactorAuth\TwoFactorAuth
 */
class TwoFactorAuth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Backstage\TwoFactorAuth\TwoFactorAuth::class;
    }
}
