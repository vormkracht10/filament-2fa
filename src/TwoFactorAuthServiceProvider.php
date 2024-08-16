<?php

namespace Vormkracht10\TwoFactorAuth;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\TwoFactorLoginResponse;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Vormkracht10\TwoFactorAuth\Commands\TwoFactorAuthCommand;
use Vormkracht10\TwoFactorAuth\Http\Livewire\Auth\Login;
use Vormkracht10\TwoFactorAuth\Http\Livewire\Auth\LoginTwoFactor;
use Vormkracht10\TwoFactorAuth\Http\Livewire\Auth\PasswordConfirmation;
use Vormkracht10\TwoFactorAuth\Http\Livewire\Auth\PasswordReset;
use Vormkracht10\TwoFactorAuth\Http\Livewire\Auth\RequestPasswordReset;
use Vormkracht10\TwoFactorAuth\Http\Responses\LoginResponse;
use Vormkracht10\TwoFactorAuth\Http\Responses\TwoFactorChallengeViewResponse;
use Vormkracht10\TwoFactorAuth\Pages\TwoFactor;
use Vormkracht10\TwoFactorAuth\Testing\TestsTwoFactorAuth;

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
        /**
         * This is the default Fortify configuration. These seem not to be used
         * in the application. I will leave them here for reference.
         */
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        config([
            'filament.auth.pages.login' => Login::class,
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
            return app()->call(Login::class);
        });

        if (Features::enabled(Features::resetPasswords())) {
            Fortify::requestPasswordResetLinkView(function () {
                return app()->call(RequestPasswordReset::class);
            });

            Fortify::resetPasswordView(function ($request) {
                return app()->call(PasswordReset::class);
            });
        }

        if (Features::enabled(Features::emailVerification())) {
            Fortify::verifyEmailView(function () {
                return view('filament-two-factor-auth::auth.verify-email');
            });
        }

        Fortify::confirmPasswordView(function () {
            return app()->call(PasswordConfirmation::class);
        });

        if (Features::enabled(Features::twoFactorAuthentication())) {
            Fortify::twoFactorChallengeView(function () {
                return app()->call(LoginTwoFactor::class);
            });
        }
    }

    protected function registerContractsAndComponents(): void
    {
        Livewire::component((new PasswordReset)->getName(), PasswordReset::class);
        Livewire::component((new RequestPasswordReset)->getName(), RequestPasswordReset::class);
        Livewire::component((new LoginTwoFactor)->getName(), LoginTwoFactor::class);
        Livewire::component((new TwoFactor)->getName(), TwoFactor::class);

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
            'create_filament-two-factor-auth_table',
        ];
    }
}
