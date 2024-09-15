<?php

namespace Vormkracht10\TwoFactorAuth\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class ForceTwoFactor
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Filament::auth()->user();

        if ($request->is('*/two-factor') || $request->is('*/logout')) {
            return $next($request);
        }

        if ($user && ! $user->two_factor_confirmed_at) {
            $currentPanel = Filament::getCurrentPanel();

            if ($currentPanel) {
                return redirect()->to(route('filament.' . $currentPanel->getId() . '.pages.two-factor', [
                    'tenant' => Filament::getTenant(),
                ]))->with('two_factor_redirect_message', __('Your administrator requires you to enable two-factor authentication.'));
            }
        }

        return $next($request);
    }
}
