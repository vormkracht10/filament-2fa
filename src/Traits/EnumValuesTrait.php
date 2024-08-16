<?php

namespace Vormkracht10\TwoFactorAuth\Traits;

trait EnumValuesTrait
{
    abstract public static function cases(): array;

    public static function values(): array
    {
        return array_map(fn ($enum) => $enum->value, static::cases());
    }
}
