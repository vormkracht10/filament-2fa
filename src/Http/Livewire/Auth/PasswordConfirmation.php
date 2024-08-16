<?php

namespace Vormkracht10\TwoFactorAuth\Http\Livewire\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PasswordConfirmation extends Component implements HasForms
{
    use InteractsWithForms;

    public function mount(): void
    {
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
            TextInput::make('password')
                ->extraInputAttributes(['name' => 'password'])
                ->label(__('Password'))
                ->password()
                ->required(),
        ];
    }

    public function render(): View
    {
        return view('filament-two-factor-auth::auth.password-confirmation')
            ->layout('filament::components.layouts.app', [
                'title' => __('Password Confirmation'),
                'breadcrumbs' => [
                    __('Password Confirmation'),
                ],
            ]);
    }
}
