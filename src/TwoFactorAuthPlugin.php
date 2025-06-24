<?php

namespace Backstage\TwoFactorAuth;

use Backstage\TwoFactorAuth\Http\Middleware\ForceTwoFactor;
use Backstage\TwoFactorAuth\Pages\TwoFactor;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;

class TwoFactorAuthPlugin implements Plugin
{
    use EvaluatesClosures;

    private Closure | bool | null $forced = false;

    private Closure | bool $showInUserMenu = true;

    public function getId(): string
    {
        return 'filament-2fa';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->login(config('filament-2fa.login'))
            ->pages([
                config('filament-2fa.two_factor_settings'),
                config('filament-2fa.challenge'),
            ]);

        if ($this->isForced()) {
            $middlewareMethod = config('filament-2fa.enabled_features.multi_tenancy') ? 'tenantMiddleware' : 'middleware';
            $panel->$middlewareMethod([
                ForceTwoFactor::class,
            ]);
        }

        if (! config('filament-2fa.enabled_features.multi_tenancy') && $this->shouldShowInUserMenu()) {
            $panel->userMenuItems([
                'two-factor-authentication' => MenuItem::make()
                    ->icon('heroicon-o-lock-closed')
                    ->label(fn (): string => __('Two-Factor Authentication'))
                    ->url(fn (): string => TwoFactor::getUrl()),
            ]);
        }

        if (config('filament-2fa.enabled_features.register')) {
            $panel->registration(config('filament-2fa.register'));
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

    public function forced(Closure | bool | null $forced = true, bool $withTenancy = false): self
    {
        $this->forced = $forced;

        return $this;
    }

    public function isForced(): Closure | bool | null
    {
        return $this->evaluate($this->forced);
    }

    public function showInUserMenu(Closure | bool $showInUserMenu = true): self
    {
        $this->showInUserMenu = $showInUserMenu;

        return $this;
    }

    public function shouldShowInUserMenu(): bool
    {
        return $this->evaluate($this->showInUserMenu);
    }
}
