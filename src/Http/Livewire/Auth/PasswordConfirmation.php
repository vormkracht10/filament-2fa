<?php

namespace Vormkracht10\TwoFactorAuth\Http\Livewire\Auth;

use Livewire\Component;
use Illuminate\Contracts\View\View;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;

class PasswordConfirmation extends Component implements HasForms
{
    use InteractsWithForms;

    public function mount(): void
    {
        $this->form->fill();

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
                ->label(__('filament-two-factor-auth::Password'))
                ->password()
                ->required(),
        ];
    }

    public function render(): View
    {
        return view('filament-two-factor-auth::auth.password-confirmation')
            ->layout('filament::components.layouts.app', [
                'title' => __('filament-two-factor-auth::Password Confirmation'),
                'breadcrumbs' => [
                    __('filament-two-factor-auth::Password Confirmation'),
                ],
            ]);
    }
}
