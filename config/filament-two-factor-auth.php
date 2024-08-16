<?php

use Vormkracht10\TwoFactorAuth\Enums\TwoFactorType;

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
];
