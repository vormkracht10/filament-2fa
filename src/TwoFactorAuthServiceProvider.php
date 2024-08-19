<?php

namespace Vormkracht10\TwoFactorAuth;

use Livewire\Livewire;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Features;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Asset;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Redirect;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\RateLimiter;
use Filament\Support\Assets\AlpineComponent;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Laravel\Fortify\Http\Responses\TwoFactorLoginResponse;
use Vormkracht10\TwoFactorAuth\Testing\TestsTwoFactorAuth;
use Vormkracht10\TwoFactorAuth\Http\Responses\LoginResponse;
use Vormkracht10\TwoFactorAuth\Commands\TwoFactorAuthCommand;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Vormkracht10\TwoFactorAuth\Http\Responses\TwoFactorChallengeViewResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;

class TwoFactorAuthServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-two-factor-auth';

    public static string $viewNamespace = 'filament-two-factor-auth';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('vormkracht10/filament-two-factor-auth');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
            $this->loadJsonTranslationsFrom($package->basePath('/../resources/lang'));
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-two-factor-auth/{$file->getFilename()}"),
                ], 'filament-two-factor-auth-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsTwoFactorAuth);

        $this->forceFortifyConfig();

        $this->registerContractsAndComponents();

        $this->defineRateLimiters();

        $this->overrideFortifyViews();

        Route::domain(config('filament.domain'))
            ->middleware(config('filament.middleware.base'))
            ->name('filament.')
            ->group(function () {
                /**
                 * We do not need to override logout response and logout path as:
                 * - logout response for both filament and fortify does
                 *    basically the same things except fortify handle for api calls
                 * - for api calls still can use POST fortify's /logout route
                 * - filament's logout route is at /filament/logout
                 */

                /**
                 * Redeclare filament.auth.login route as fortify override it
                 * This route name is used multiple places in filament.
                 */
                Route::prefix(config('filament.path'))->group(function () {
                    Route::get('/filament-login', fn () => Redirect::route('login'))
                        ->name('auth.login');
                });
            });
    }

    protected function forceFortifyConfig(): void
    {
        config([
            'filament.auth.pages.login' => config('filament-two-factor-auth.login'),
            'fortify.views' => true,
            'fortify.home' => config('filament.home_url'),
            'forms.dark_mode' => config('filament.dark_mode'),
        ]);
    }

    protected function defineRateLimiters(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }

    protected function overrideFortifyViews(): void
    {
        Fortify::loginView(function () {
            return app()->call(config('filament.auth.pages.login'));
        });

        if (Features::enabled(Features::resetPasswords())) {
            Fortify::requestPasswordResetLinkView(function () {
                return app()->call(config('filament-two-factor-auth.request_password_reset'));
            });

            Fortify::resetPasswordView(function ($request) {
                return app()->call(config('filament-two-factor-auth.password_reset'));
            });
        }

        if (Features::enabled(Features::emailVerification())) {
            Fortify::verifyEmailView(function () {
                return view('filament-two-factor-auth::auth.verify-email');
            });
        }

        Fortify::confirmPasswordView(function () {
            return app()->call(config('filament-two-factor-auth.password_confirmation'));
        });

        if (Features::enabled(Features::twoFactorAuthentication())) {
            Fortify::twoFactorChallengeView(function () {
                return app()->call(config('filament-two-factor-auth.challenge'));
            });
        }
    }

    protected function registerContractsAndComponents(): void
    {
        Livewire::component(
            'password-reset',
            config('filament-two-factor-auth.password_reset')
        );
        Livewire::component(
            'request-password-reset',
            config('filament-two-factor-auth.request_password_reset')
        );
        Livewire::component(
            'login-two-factor',
            config('filament-two-factor-auth.challenge')
        );
        Livewire::component(
            'two-factor',
            config('filament-two-factor-auth.two_factor_settings')
        );

        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(TwoFactorLoginResponseContract::class, TwoFactorLoginResponse::class);
        $this->app->singleton(TwoFactorChallengeViewResponse::class, TwoFactorChallengeViewResponse::class);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'vormkracht10/filament-two-factor-auth';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-two-factor-auth', __DIR__ . '/../resources/dist/components/filament-two-factor-auth.js'),
            Css::make('filament-two-factor-auth-styles', __DIR__ . '/../resources/dist/filament-two-factor-auth.css'),
            Js::make('filament-two-factor-auth-scripts', __DIR__ . '/../resources/dist/filament-two-factor-auth.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            TwoFactorAuthCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'add_two_factor_type_column_to_users_table',
        ];
    }
}
