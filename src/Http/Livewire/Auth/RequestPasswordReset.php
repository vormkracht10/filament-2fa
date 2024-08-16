<?php

namespace Vormkracht10\TwoFactorAuth\Http\Livewire\Auth;

use Filament\Pages\Page;

use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;

class RequestPasswordReset extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $layout = 'filament-two-factor-auth::layouts.login';

    public ?string $email = '';

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill();

        if (session('status')) {
            Notification::make()
                ->title(session('status'))
                ->success()
                ->send();

            redirect()->route('login');
        }
    }

    public function hasLogo(): bool
    {
        return false;
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('email')
                ->extraInputAttributes(['name' => 'email'])
                ->label('E-mail')
                ->email()
                ->required()
                ->autocomplete(),
        ];
    }

    public function render(): View
    {
        return view('filament-two-factor-auth::auth.request-password-reset', $this->getViewData())
            ->layout($this->getLayout(), [
                'livewire' => $this,
                'maxContentWidth' => $this->getMaxContentWidth(),
                ...$this->getLayoutData(),
            ]);
    }
}
