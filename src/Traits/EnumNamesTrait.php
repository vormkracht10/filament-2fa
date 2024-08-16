<?php

namespace Vormkracht10\TwoFactorAuth\Traits;

trait EnumNamesTrait
{
    abstract public static function cases(): array;

    public static function names(): array
    {
        return array_map(fn ($enum) => $enum->name, static::cases());
    }
}
