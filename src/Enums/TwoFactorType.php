<?php

namespace Vormkracht10\TwoFactorAuth\Enums;

use Vormkracht10\TwoFactorAuth\Traits\EnumArraySerializableTrait;

enum TwoFactorType: string
{
    use EnumArraySerializableTrait;

    case authenticator = 'authenticator';
    case email = 'email';
    case phone = 'phone';

    public static function values(): array
    {
        return array_map(fn ($type) => $type->label(), self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::email => __('Email'),
            self::authenticator => __('Authenticator app'),
            self::phone => __('SMS'),
        };
    }
}
