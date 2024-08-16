<?php

namespace Vormkracht10\TwoFactorAuth\Traits;

trait EnumValuesTrait
{
    public static function values(): array
    {
        return array_map(fn ($enum) => $enum->value, static::cases());
    }
}
