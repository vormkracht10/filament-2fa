<?php

namespace Vormkracht10\TwoFactorAuth\Http\Livewire\Auth;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Actions\Action;
use App\Notifications\SendOTP;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;

class LoginTwoFactor extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithFormActions;
    use WithRateLimiting;

    protected static string $layout = 'filament-two-factor-auth::layouts.login';

    protected static string $view = 'filament-two-factor-auth::auth.login-two-factor';

    public ?User $challengedUser = null;

    public function mount(TwoFactorLoginRequest $request): void
    {
        if ($request->challengedUser()) {
            $this->challengedUser = $request->challengedUser();
        }

        $this->form->fill();
    }

    public function hasLogo(): bool
    {
        return false;
    }

    public function resend(): ?Action
    {
        return Action::make('resend')
            ->label(__('filament-two-factor-auth::Resend'))
            ->color('info')
            ->extraAttributes(['class' => 'w-full text-xs'])
            ->link()
            ->action(function () {
                if (!$this->throttle()) {
                    return;
                }

                $this->challengedUser->notify(app(SendOTP::class));

                Notification::make()
                    ->title(__('filament-two-factor-auth::Successfully resend the OTP code'))
                    ->success()
                    ->send();
            });
    }

    private function throttle(): bool
    {
        try {
            $this->rateLimit(1);
            return true;
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/email-verification/email-verification-prompt.notifications.notification_resend_throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => $exception->minutesUntilAvailable,
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/email-verification/email-verification-prompt.notifications.notification_resend_throttled') ?: []) ? __('filament-panels::pages/auth/email-verification/email-verification-prompt.notifications.notification_resend_throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => $exception->minutesUntilAvailable,
                ]) : null)
                ->danger()
                ->send();
            return false;
        }
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('code')
                ->extraInputAttributes(['name' => 'code'])
                ->label(__('filament-two-factor-auth::Code')),
            TextInput::make('recovery_code')
                ->extraInputAttributes(['name' => 'recovery_code'])
                ->label(__('filament-two-factor-auth::Recovery code')),

        ];
    }
}
