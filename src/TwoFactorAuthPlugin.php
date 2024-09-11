<?php

namespace Vormkracht10\TwoFactorAuth;

use Filament\Contracts\Plugin;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Vormkracht10\TwoFactorAuth\Http\Middleware\Force2Factor;
use Vormkracht10\TwoFactorAuth\Pages\TwoFactor;

class TwoFactorAuthPlugin implements Plugin
{
    private bool $forced = false;

    public function getId(): string
    {
        return 'filament-two-factor-auth';
    }

    public function forced(bool $forced = true): self
    {
        $this->forced = $forced;

        return $this;
    }

    public function isForced(): bool
    {
        return $this->forced;
    }

    public function register(Panel $panel): void
    {
        $panel
            ->login(config('filament-two-factor-auth.login'))
            ->pages([
                config('filament-two-factor-auth.two_factor_settings'),
                config('filament-two-factor-auth.challenge'),
            ])
            ->viteTheme('vendor/vormkracht10/filament-2fa/resources/dist/filament-two-factor-auth.css');

        if ($this->isForced()) {
            $panel->tenantMiddleware([
                Force2Factor::class,
            ]);
        }

        if (! config('filament-two-factor-auth.enabled_features.multi_tenancy')) {
            $panel->userMenuItems([
                'two-factor-authentication' => MenuItem::make()
                    ->icon('heroicon-o-lock-closed')
                    ->label(__('Two-Factor Authentication'))
                    ->url(fn(): string => TwoFactor::getUrl()),
            ]);
        }

        if (config('filament-two-factor-auth.enabled_features.register')) {
            $panel->registration(config('filament-two-factor-auth.register'));
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}