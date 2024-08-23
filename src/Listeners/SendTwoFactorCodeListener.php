<?php

namespace Vormkracht10\TwoFactorAuth\Listeners;

use Vormkracht10\TwoFactorAuth\Notifications\SendOTP;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;

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
        /** @var object $user */
        $user = $event->user;
        $user->notify(app(SendOTP::class));
    }
}