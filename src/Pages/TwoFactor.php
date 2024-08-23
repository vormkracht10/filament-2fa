<?php

namespace Vormkracht10\TwoFactorAuth\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;
use Vormkracht10\TwoFactorAuth\Enums\TwoFactorType;

class TwoFactor extends Page implements HasForms
{
    /**
     * @var array<string, mixed>
     */
    public array $twoFactorData = [];

    /**
     * @var array<string, mixed>
     */
    public array $otpCodeData = [];

    public bool $showingQrCode = false;

    public bool $showingConfirmation = false;

    public bool $showingRecoveryCodes = false;

    public bool $showQrCode = false;

    public string $code;

    public ?int $twoFactorOptionsCount = null;

    public mixed $user = null;

    protected static string $view = 'filament-two-factor-auth::two-factor';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getTitle(): string | Htmlable
    {
        return __('Two-Factor Authentication');
    }

    public function mount(): void
    {
        $this->twoFactorOptionsCount = config('filament-two-factor-auth.options') ? count(config('filament-two-factor-auth.options')) : 0;

        $this->user = Auth::user();

        if (
            Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm') &&
            is_null($this->user->two_factor_confirmed_at)
        ) {
            app(DisableTwoFactorAuthentication::class)($this->user);
        }

        if (session('status') == 'two-factor-authentication-enabled') {
            Notification::make()
                ->title(__('Two-Factor Authentication enabled'))
                ->success()
                ->send();
        }
    }

    /**
     * @return array<int, \Filament\Forms\Components\TextInput>
     */
    public function getConfirmationForm(): array
    {
        return [
            TextInput::make('current_password')
                ->label(__('Password'))
                ->dehydrateStateUsing(fn ($state) => filled($state))
                ->required()
                ->password()
                ->inlineLabel()
                ->rule('current_password'),
        ];
    }

    public function twoFactorOptionForm(Form $form): Form
    {
        return $form->schema([
            Radio::make('option')
                ->label(__('Authentication method'))
                ->hiddenLabel()
                ->options(
                    /** @var \Illuminate\Support\Collection<int, TwoFactorType> $options */
                    collect(config('filament-two-factor-auth.options', [
                        TwoFactorType::email,
                        TwoFactorType::phone,
                        TwoFactorType::authenticator,
                    ]))
                        ->mapWithKeys(function (TwoFactorType $option): array {
                            return [$option->value => $option->getLabel()];
                        })
                ),
        ])->statePath('twoFactorData');
    }

    public function otpCodeForm(Form $form): Form
    {
        return $form->schema([
            TextInput::make('code')
                ->label(__('Code'))
                ->validationAttribute('OTP code')
                ->inlineLabel()
                ->required(),
        ])->statePath('otpCodeData');
    }

    protected function getForms(): array
    {
        return [
            'twoFactorOptionForm',
            'otpCodeForm',
        ];
    }

    public function enableAction(): Action
    {
        return Action::make('enable')
            ->label(__('Activate'))
            ->color('primary')
            ->action(function ($data) {
                $formData = [];

                if (isset($data['email'])) {
                    $formData['email'] = $data['email'];
                }

                if ($this->twoFactorData['option']) {
                    $formData['two_factor_type'] = TwoFactorType::tryFrom($this->twoFactorData['option']);
                }

                /** @var array{two_factor_type: TwoFactorType|null, email?: mixed} $formData */
                if (
                    isset($formData['two_factor_type']) &&
                    ($formData['two_factor_type'] === TwoFactorType::email || $formData['two_factor_type'] === TwoFactorType::phone)
                ) {
                    $this->showQrCode = false;
                } else {
                    $this->showQrCode = true;
                }

                $this->user->update($formData);

                $this->enableTwoFactorAuthentication(app(EnableTwoFactorAuthentication::class));
            });
    }

    public function confirmAction(): Action
    {
        return Action::make('confirm')
            ->label(__('Confirm'))
            ->color('primary')
            ->action(function ($data) {
                $this->confirmTwoFactorAuthentication(app(ConfirmTwoFactorAuthentication::class));
            });
    }

    public function regenerateAction(): Action
    {
        return Action::make('regenerate')
            ->label(__('Generate new recovery codes'))
            ->color('primary')
            ->action(function () {
                $this->regenerateRecoveryCodes(app(GenerateNewRecoveryCodes::class));
            });
    }

    public function disableAction(): Action
    {
        return Action::make('disable')
            ->label($this->user->two_factor_confirmed_at ? __('Deactivate') : __('Cancel'))
            ->color('danger')
            ->action(function ($data) {
                $this->disableTwoFactorAuthentication(app(DisableTwoFactorAuthentication::class));
            });
    }

    /**
     * This method is used in the view
     *
     * @phpstan-ignore-next-line
     * */
    private function showTwoFactor(): bool
    {
        return ! empty($this->user->two_factor_secret);
    }

    public function enableTwoFactorAuthentication(EnableTwoFactorAuthentication $enable): void
    {
        $enable($this->user);

        $this->showingQrCode = true;

        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm')) {
            $this->showingConfirmation = true;
        } else {
            $this->showingRecoveryCodes = true;
        }
    }

    public function confirmTwoFactorAuthentication(ConfirmTwoFactorAuthentication $confirm): void
    {
        $confirm($this->user, $this->otpCodeData['code']);

        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = true;
    }

    public function disableTwoFactorAuthentication(DisableTwoFactorAuthentication $disable): void
    {
        $disable($this->user);

        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = false;
    }

    public function showRecoveryCodes(): void
    {
        $this->showingRecoveryCodes = true;
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate): void
    {
        $generate($this->user);

        $this->showingRecoveryCodes = true;
    }
}
