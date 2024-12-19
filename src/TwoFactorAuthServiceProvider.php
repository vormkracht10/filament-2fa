<?php

namespace Vormkracht10\TwoFactorAuth;

use Filament\Facades\Filament;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Vormkracht10\TwoFactorAuth\Commands\TwoFactorAuthCommand;
use Vormkracht10\TwoFactorAuth\Enums\TwoFactorType;
use Vormkracht10\TwoFactorAuth\Http\Responses\LoginResponse;
use Vormkracht10\TwoFactorAuth\Http\Responses\TwoFactorChallengeViewResponse;
use Vormkracht10\TwoFactorAuth\Http\Responses\TwoFactorLoginResponse;
use Vormkracht10\TwoFactorAuth\Testing\TestsTwoFactorAuth;

class TwoFactorAuthServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-2fa';

    public static string $viewNamespace = 'filament-2fa';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) use ($package) {
                $command
                    ->startWith(function (InstallCommand $command) use ($package) {
                        if ($command->confirm('Would you like to publish the config file?', true)) {
                            $command->comment('Publishing config...');
                            $command->callSilently('vendor:publish', [
                                '--tag' => "{$package->shortName()}-config",
                            ]);
                        }

                        if ($command->confirm('Would you like to publish the migrations?', true)) {
                            $command->comment('Publishing migrations...');
                            $command->callSilently('vendor:publish', [
                                '--tag' => "{$package->shortName()}-migrations",
                            ]);
                        }

                        if ($command->confirm('Would you like to run the migrations now?', true)) {
                            $command->comment('Running migrations...');

                            $command->call('migrate');

                            if ($command->confirm('Would you like us to set the two factor type to "authenticator" for existing users?', true)) {

                                if (! Schema::hasTable('users')) {
                                    $command->error('Table users does not exist.');

                                    return;
                                }

                                if (! Schema::hasColumn('users', 'two_factor_type')) {
                                    $command->error('Column two_factor_type does not exist in table users. Please run the migrations first.');

                                    return;
                                }

                                $command->comment('Setting two factor type to "authenticator" for existing users...');

                                DB::table('users')
                                    ->where('two_factor_confirmed_at', '!=', null)
                                    ->update(['two_factor_type' => TwoFactorType::authenticator]);
                            }
                        }
                    })
                    ->askToStarRepoOnGitHub('vormkracht10/filament-2fa');
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

    public function packageRegistered(): void
    {
        $this->forceFortifyConfig();
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        $colors = Filament::getCurrentPanel()?->getColors();
        $color = isset($colors['primary'])
            ? (is_string($colors['primary']) ? Color::hex($colors['primary']) : $colors['primary'])
            : \Filament\Support\Colors\Color::Amber;

        FilamentColor::register([
            'default' => $color,
        ]);

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
                    $file->getRealPath() => base_path("stubs/filament-2fa/{$file->getFilename()}"),
                ], 'filament-2fa-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsTwoFactorAuth);

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
            'filament.auth.pages.login' => config('filament-2fa.login'),
            'fortify.prefix' => 'fortify',
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
                return app()->call(config('filament-2fa.request_password_reset'));
            });

            Fortify::resetPasswordView(function ($request) {
                return app()->call(config('filament-2fa.password_reset'));
            });
        }

        if (Features::enabled(Features::emailVerification())) {
            Fortify::verifyEmailView(function () {
                return view('filament-2fa::auth.verify-email');
            });
        }

        Fortify::confirmPasswordView(function () {
            return app()->call(config('filament-2fa.password_confirmation'));
        });

        if (Features::enabled(Features::twoFactorAuthentication())) {
            Fortify::twoFactorChallengeView(function () {
                return app()->call(config('filament-2fa.challenge'));
            });
        }
    }

    protected function registerContractsAndComponents(): void
    {
        Livewire::component(
            'password-reset',
            config('filament-2fa.password_reset')
        );
        Livewire::component(
            'request-password-reset',
            config('filament-2fa.request_password_reset')
        );
        Livewire::component(
            'login-two-factor',
            config('filament-2fa.challenge')
        );
        Livewire::component(
            'two-factor',
            config('filament-2fa.two_factor_settings')
        );

        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(TwoFactorLoginResponseContract::class, TwoFactorLoginResponse::class);
        $this->app->singleton(TwoFactorChallengeViewResponse::class, TwoFactorChallengeViewResponse::class);
    }

    protected function getAssetPackageName(): string
    {
        return 'vormkracht10/filament-2fa';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-2fa', __DIR__ . '/../resources/dist/components/filament-2fa.js'),
            // Css::make('filament-2fa-styles', __DIR__ . '/../resources/dist/filament-2fa.css'),
            // Js::make('filament-2fa-scripts', __DIR__ . '/../resources/dist/filament-2fa.js'),
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
