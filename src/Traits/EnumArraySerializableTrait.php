<?php

namespace Vormkracht10\TwoFactorAuth\Traits;

trait EnumArraySerializableTrait
{
    use EnumNamesTrait;
    use EnumValuesTrait;

    /**
     * Get the enum as an array.
     *
     * @return array<string, string>
     */
    public static function array(): array
    {
        return array_combine(static::names(), static::values());
    }
}
