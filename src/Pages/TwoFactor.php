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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

    protected static string $view = 'filament-2fa::two-factor';

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
        if (session('two_factor_redirect_message')) {
            Notification::make()
                ->title(__('Two-Factor Authentication mandatory'))
                ->body(session('two_factor_redirect_message'))
                ->danger()
                ->persistent()
                ->send();
        }

        if (config('filament-2fa.options')) {
            $this->twoFactorOptionsCount = count(config('filament-2fa.options'));
        } else {
            $this->twoFactorOptionsCount = 0;
        }

        $this->user = Auth::user();

        if (
            $this->user !== null &&
            Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm') &&
            is_null($this->user->two_factor_confirmed_at)
        ) {
            app(DisableTwoFactorAuthentication::class)($this->user);
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
        /** @var array<int, TwoFactorType> $configOptions */
        $configOptions = config('filament-2fa.options', [
            TwoFactorType::email,
            TwoFactorType::phone,
            TwoFactorType::authenticator,
        ]);

        /** @var Collection<int,TwoFactorType> $collection */
        $collection = collect($configOptions);

        /** @var array<string, string> $options */
        $options = $collection
            ->mapWithKeys(function (TwoFactorType $option): array {
                return [$option->value => $option->getLabel()];
            })->toArray();

        return $form->schema([
            Radio::make('option')
                ->label(__('Authentication method'))
                ->hiddenLabel()
                ->options($options),
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

    public function translatedType(string $type): string
    {
        return match ($type) {
            TwoFactorType::email->value => __('email'),
            TwoFactorType::phone->value => __('phone'),
            TwoFactorType::authenticator->value => __('authenticator app'),
            default => $type,
        };
    }

    public function enableAction(): Action
    {
        $action = Action::make('enable')
            ->color('primary')
            ->label(__('Activate'));

        if (isset($this->twoFactorData['option']) && $this->twoFactorData['option'] !== TwoFactorType::authenticator->value) {
            $action->requiresConfirmation()
                ->modalHeading(__('Activate Two-Factor Authentication'))
                ->modalDescription(__('Please confirm this is your :type before continuing.', [
                    'type' => $this->translatedType($this->twoFactorData['option']),
                ]))
                ->form(function (): array {
                    $fields = $this->getConfirmableFields();
                    $option = $this->twoFactorData['option'];

                    if (isset($fields[$option])) {
                        $field = $fields[$option];

                        return [
                            TextInput::make($field['name'])
                                ->label($field['label'])
                                ->default($field['default'])
                                ->required()
                                ->rules($field['rules'])
                                ->inlineLabel(),
                        ];
                    }

                    return [];
                });
        }

        return $action->action(function (array $data): void {
            $formData = [];

            if (isset($data['email'])) {
                $formData['email'] = $data['email'];
            }

            try {
                if ($this->twoFactorData['option']) {
                    $formData['two_factor_type'] = TwoFactorType::tryFrom($this->twoFactorData['option']);
                }
            } catch (\Exception $e) {
                Notification::make()
                    ->title('Error!')
                    ->body(__('Please select a method of authentication.'))
                    ->danger()
                    ->send();

                return;
            }            /** @var array{two_factor_type: TwoFactorType|null, email?: mixed} $formData */
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

    /** @return array<int|string, array{name: 'authenticator'|'email'|'phone', label: string, default: mixed, rules: array{0: 'required', 1?: 'email'}}> */
    private function getConfirmableFields(): array
    {
        $confirmableOptions = config('filament-2fa.options', [
            TwoFactorType::email,
            TwoFactorType::phone,
        ]);

        $fields = [];

        foreach ($confirmableOptions as $option) {
            switch ($option) {
                case TwoFactorType::authenticator:
                    $fields[$option->value] = [
                        'name' => 'authenticator',
                        'label' => __('Authenticator'),
                        'default' => null, // Set default value if needed
                        'rules' => ['required'],
                    ];

                    break;
                case TwoFactorType::email:
                    $fields[$option->value] = [
                        'name' => 'email',
                        'label' => __('Email'),
                        'default' => $this->user->email,
                        'rules' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user->id)],
                    ];

                    break;
                case TwoFactorType::phone:
                    $fields[$option->value] = [
                        'name' => 'phone',
                        'label' => __('Phone number'),
                        'default' => $this->user->{config('filament-2fa.phone_number_field', 'phone')},
                        'rules' => ['required'],
                    ];

                    break;
            }
        }

        return $fields;
    }

    public function confirmAction(): Action
    {
        return Action::make('confirm')
            ->label(__('Confirm'))
            ->color('primary')
            ->action(function ($data) {
                if (count($this->otpCodeData) === 0) {
                    $this->throwFailureValidationException();
                }

                $this->confirmTwoFactorAuthentication(app(ConfirmTwoFactorAuthentication::class));
            });
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'otpCodeData.code' => __('The code you entered is invalid.'),
        ]);
    }

    public function regenerateAction(): Action
    {
        return Action::make('regenerate')
            ->label(__('Regenerate'))
            ->color('primary')
            ->action(function () {
                $this->regenerateRecoveryCodes(app(GenerateNewRecoveryCodes::class));
            });
    }

    public function downloadAction(): Action
    {
        return Action::make('download')
            ->label(__('Download'))
            ->color('primary')
            ->action(function () {
                return response()->streamDownload(function () {
                    echo implode(PHP_EOL, $this->user->recoveryCodes());
                }, 'recovery-codes.txt');
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
        try {
            $confirm($this->user, $this->otpCodeData['code']);

            Notification::make()
                ->title(__('Two-Factor Authentication activated'))
                ->body(__('From now on, you will be asked for a code when you log in.'))
                ->success()
                ->duration(5000)
                ->send();

            $this->showingQrCode = false;
            $this->showingConfirmation = false;
            $this->showingRecoveryCodes = true;
        } catch (\Exception $e) {
            $this->throwFailureValidationException();
        }
    }

    public function disableTwoFactorAuthentication(DisableTwoFactorAuthentication $disable): void
    {
        if ($this->user->two_factor_confirmed_at) {
            Notification::make()
                ->title(__('Two-Factor Authentication deactivated'))
                ->body(__('You can now log in without a code.'))
                ->success()
                ->duration(5000)
                ->send();
        }
        $disable($this->user);

        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = false;

        $this->user->refresh();
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
