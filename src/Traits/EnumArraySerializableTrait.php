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
        $names = static::names();

        $values = array_filter(static::values(), fn ($value) => $value !== null);

        return array_combine($names, $values);
    }
}
