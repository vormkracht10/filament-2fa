<?php

namespace Vormkracht10\TwoFactorAuth\Http\Livewire\Auth;

use Filament\Actions\Action;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Features;
use Filament\Facades\Filament;
use Illuminate\Routing\Pipeline;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Vormkracht10\TwoFactorAuth\Http\Responses\LoginResponse;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Vormkracht10\TwoFactorAuth\Http\Middleware\RedirectIfTwoFactorAuthenticatable;

class Login extends BaseLogin
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament-two-factor-auth::auth.login';

    public $email = '';

    public $password = '';

    public $resetPasswordEnabled = false;

    public $registrationEnabled = false;

    public function mount(): void
    {
        parent::mount();

        $this->resetPasswordEnabled = Features::enabled(Features::resetPasswords());
        $this->registrationEnabled = Features::enabled(Features::registration());

        if (session('status')) {
            Notification::make()
                ->title(session('status'))
                ->success()
                ->send();
        }
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('email')
                ->extraInputAttributes(['name' => 'email'])
                ->label(__('filament::login.fields.email.label'))
                ->email()
                ->required()
                ->autocomplete(),
            TextInput::make('password')
                ->extraInputAttributes(['name' => 'password'])
                ->label(__('filament::login.fields.password.label'))
                ->password()
                ->required(),
            Checkbox::make('remember')
                ->extraInputAttributes(['name' => 'remember'])
                ->label(__('filament::login.fields.remember.label')),
        ];
    }

    public function loginWithFortify()
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        $request = request()->merge($data);

        return $this->loginPipeline($request)->then(function (Request $request) use ($data) {

            if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
                $this->throwFailureValidationException();
            }

            $user = Filament::auth()->user();

            if (
                ($user instanceof FilamentUser) &&
                (! $user->canAccessPanel(Filament::getCurrentPanel()))
            ) {
                Filament::auth()->logout();

                $this->throwFailureValidationException();
            }

            session()->regenerate();

            return app(LoginResponse::class);
        });
    }

    protected function loginPipeline(Request $request)
    {
        if (Fortify::$authenticateThroughCallback) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                call_user_func(Fortify::$authenticateThroughCallback, $request)
            ));
        }

        if (is_array(config('fortify.pipelines.login'))) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                config('fortify.pipelines.login')
            ));
        }

        return (new Pipeline(app()))->send($request)->through(array_filter([
            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
            Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]));
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/login.form.password.label'))
            ->hint(filament()->hasPasswordReset() ? new HtmlString(Blade::render('<x-filament::link href="/forgot-password"> {{ __(\'filament-panels::pages/auth/login.actions.request_password_reset.label\') }}</x-filament::link>')) : null)
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->color('info')
            ->label(__('filament-panels::pages/auth/login.form.actions.authenticate.label'))
            ->submit('authenticate');
    }
}
