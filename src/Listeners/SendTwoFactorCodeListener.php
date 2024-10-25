<?php

namespace Vormkracht10\TwoFactorAuth\Listeners;

use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Vormkracht10\TwoFactorAuth\Notifications\SendOTP;

class SendTwoFactorCodeListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TwoFactorAuthenticationChallenged | TwoFactorAuthenticationEnabled $event): void
    {
        /** @var mixed $user */
        $user = $event->user;
        $user->notify(app(config('filament-2fa.send_otp_class') ?? SendOTP::class));
    }
}
