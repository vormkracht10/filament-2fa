<?php

namespace Vormkracht10\TwoFactorAuth\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class RegistrationResponse implements Responsable
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        return redirect()->intended(route('filament.admin.auth.email-verification.prompt'));
    }
}
