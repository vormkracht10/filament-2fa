<?php

use Vormkracht10\TwoFactorAuth\Enums\TwoFactorType;
use Vormkracht10\TwoFactorAuth\Http\Livewire\Auth\Login;
use Vormkracht10\TwoFactorAuth\Http\Livewire\Auth\LoginTwoFactor;
use Vormkracht10\TwoFactorAuth\Http\Livewire\Auth\PasswordConfirmation;
use Vormkracht10\TwoFactorAuth\Http\Livewire\Auth\PasswordReset;
use Vormkracht10\TwoFactorAuth\Http\Livewire\Auth\RequestPasswordReset;
use Vormkracht10\TwoFactorAuth\Pages\TwoFactor;

return [

    /*
    |--------------------------------------------------------------------------
    | Two Factor Authentication
    |--------------------------------------------------------------------------
    |
    | This value determines which two factor authentication options are available.
    | Simply add or remove the options you want to use.
    |
    | Available options: email, phone, authenticator
    |
    */
    'options' => [
        TwoFactorType::email,
        TwoFactorType::phone,
        TwoFactorType::authenticator,
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Service
    |--------------------------------------------------------------------------
    |
    | This value determines which SMS service to use. For ready-to-use notification
    | channels, you can check out the documentation (SMS) here:
    | https://laravel-notification-channels.com/
    |
    */
    'sms_service' => null, // For example: MessageBird::class

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | If you want to customize the pages, you can override the used classes here.
    | Make your that your classes extend the original classes.
    |
    */
    'login' => Login::class,
    'challenge' => LoginTwoFactor::class,
    'two_factor_settings' => TwoFactor::class,
    'password_reset' => PasswordReset::class,
    'password_confirmation' => PasswordConfirmation::class,
    'request_password_reset' => RequestPasswordReset::class,
];
