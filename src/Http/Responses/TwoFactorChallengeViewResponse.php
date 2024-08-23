<?php

namespace Vormkracht10\TwoFactorAuth\Http\Responses;

use Filament\Facades\Filament;
use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse as LoginResponseContract;

class TwoFactorChallengeViewResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return redirect()->intended(Filament::getCurrentPanel()?->getUrl() ?? config('fortify.home'));
    }
}
