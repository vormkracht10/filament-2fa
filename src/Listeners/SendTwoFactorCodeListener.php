<?php

namespace Vormkracht10\TwoFactorAuth\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
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
    public function handle(TwoFactorAuthenticationChallenged|TwoFactorAuthenticationEnabled  $event): void
    {
        $event->user->notify(app(SendOTP::class));
    }
}