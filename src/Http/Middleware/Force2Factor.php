<?php

namespace Vormkracht10\TwoFactorAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Force2Factor
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = filament()->auth()->user();

        if ($request->is('*/two-factor') || $request->is('*/logout')) {
            return $next($request);
        }

        if (! $user->two_factor_confirmed_at) {
            // TODO: Redirect to the two-factor page.
        }

        return $next($request);
    }
}
