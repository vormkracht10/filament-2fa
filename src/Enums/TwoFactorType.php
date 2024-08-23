<?php

namespace Vormkracht10\TwoFactorAuth\Enums;

use Filament\Support\Contracts\HasLabel;
use Vormkracht10\TwoFactorAuth\Traits\EnumArraySerializableTrait;

enum TwoFactorType: string implements HasLabel
{
    use EnumArraySerializableTrait;

    case authenticator = 'authenticator';
    case email = 'email';
    case phone = 'phone';

    /**
     * Get the values of the enum.
     *
     * @return array<int, string|null>
     */
    public static function values(): array
    {
        return array_map(fn ($type) => $type->getLabel(), self::cases());
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::email => __('Email'),
            self::authenticator => __('Authenticator app'),
            self::phone => __('SMS'),
        };
    }
}
