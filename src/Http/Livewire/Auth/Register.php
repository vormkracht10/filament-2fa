<?php

namespace Backstage\TwoFactorAuth\Http\Livewire\Auth;

use Filament\Pages\Auth\Register as BaseRegister;
use Backstage\TwoFactorAuth\Http\Responses\RegistrationResponse;

class Register extends BaseRegister
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament-2fa::auth.register';

    public function register(): ?RegistrationResponse
    {
        parent::register();

        return app(RegistrationResponse::class);
    }
}
