# Filament Two Factor Authentication (2FA) plugin

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vormkracht10/filament-2fa.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/filament-2fa)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/vormkracht10/filament-2fa/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/vormkracht10/filament-2fa/actions?query=workflow%3Arun-tests+branch%3Amain)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/vormkracht10/filament-2fa/phpstan.yml?branch=main&label=phpstan&style=flat-square)](https://github.com/vormkracht10/filament-2fa/actions/workflows/phpstan.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/vormkracht10/filament-2fa.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/filament-2fa)

This package adds Two Factor Authentication for your Laravel Filament app, using the first party package Laravel Fortify. We provide the views and logic to enable Two Factor Authentication (2FA) in your Filament app. Possible authentication methods are:

-   Email
-   SMS
-   Authenticator app

## Features and screenshots

### Enable Two Factor Authentication (2FA)

![Enable Two Factor Authentication (2FA)](https://raw.githubusercontent.com/vormkracht10/filament-2fa/main/docs/two-factor-page.png)

### Using authenticator app as two factor method

![Authenticator app](https://raw.githubusercontent.com/vormkracht10/filament-2fa/main/docs/authenticator-app.png)

### Using email or SMS as two factor method

![Email or SMS](https://raw.githubusercontent.com/vormkracht10/filament-2fa/main/docs/email-or-sms.png)

### Recovery codes

![Recovery codes](https://raw.githubusercontent.com/vormkracht10/filament-2fa/main/docs/recovery-codes.png)

### Two Factor authentication challenge

![Two Factor challenge](https://raw.githubusercontent.com/vormkracht10/filament-2fa/main/docs/code-challenge.png)

## Installation

You can install the package via composer:

```bash
composer require vormkracht10/filament-2fa
```

If you don't have [Laravel Fortify](https://laravel.com/docs/11.x/fortify) installed yet, you can install it by running the following commands:

```bash
composer require laravel/fortify
```

```bash
php artisan fortify:install
```

```bash
php artisan migrate
```

You can then easily install the plugin by running the following command:

```bash
php artisan filament-two-factor-auth:install
```

Then add the plugin to your `PanelProvider`:

```php
use Vormkracht10\TwoFactorAuth\TwoFactorAuthPlugin;

// ...

->plugin(TwoFactorAuthPlugin::make())
```

Make sure your user uses the `TwoFactorAuthenticatable` trait:

```php
class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;
    // ...
}
```

Also define the `two_factor_type` cast on your user model:

```php
use Vormkracht10\TwoFactorAuth\Enums\TwoFactorType;

// ...

protected function casts(): array
{
    return [
        'two_factor_type' => TwoFactorType::class,
    ];
}
```

> ❗ When using `fillable` instead of `guarded` on your model, make sure to add `two_factor_type` to the `$fillable` array.

### Register the event listener

#### Laravel 11

In case you're using Laravel 11, you need to register the event listener in your `AppServiceProvider` boot method:

```php
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Vormkracht10\TwoFactorAuth\Listeners\SendTwoFactorCodeListener;

// ...

public function boot(): void
{
    Event::listen([
        TwoFactorAuthenticationChallenged::class,
        TwoFactorAuthenticationEnabled::class
    ], SendTwoFactorCodeListener::class);
}
```

#### Laravel < 11

In case you're not using Laravel 11 yet, you will probably need to manually register the event listener in your `EventServiceProvider`:

```php
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Vormkracht10\TwoFactorAuth\Listeners\SendTwoFactorCodeListener;

// ...

protected $listen = [
    TwoFactorAuthenticationChallenged::class => [
        SendTwoFactorCodeListener::class,
    ],
    TwoFactorAuthenticationEnabled::class => [
        SendTwoFactorCodeListener::class,
    ],
];
```

If you want to customize the views (including email), you can publish them using the following command:

```bash
php artisan vendor:publish --tag=filament-two-factor-auth-views
```

## Usage

### Configuration

The authentication methods can be configured in the `config/filament-two-factor-auth.php` file (which is published during the install command).

You can simply add or remove (comment) the methods you want to use:

```php
return [
    'options' => [
        TwoFactorType::email,
        TwoFactorType::phone,
        TwoFactorType::authenticator,
    ],

    'sms_service' => null, // For example: MessageBird::class
];
```

If you want to use the SMS method, you need to provide an SMS service. You can check the [Laravel Notifications documentation](https://laravel-notification-channels.com/about/) for ready-to-use services.

**Also make sure your user model has a `phone` attribute.**

### Customization

If you want to fully customize the pages, you can override the classes in the `config/filament-two-factor-auth.php` file:

```php
return [
    // ...

    'login' => Login::class,
    'register' => Register::class,
    'challenge' => LoginTwoFactor::class,
    'two_factor_settings' => TwoFactor::class,
    'password_reset' => PasswordReset::class,
    'password_confirmation' => PasswordConfirmation::class,
    'request_password_reset' => RequestPasswordReset::class,
];
```

Make sure you extend the original classes from the package.

### Multi-tenant setup

If you're using Filament in a multi-tenant setup, you need to set the `tenant` option to `true` in the `config/filament-two-factor-auth.php` file. You also need to set the `userMenuItems` in your panel config. Take a look at the example below:

```php
use Vormkracht10\TwoFactorAuth\Pages\TwoFactor;

// ...

->userMenuItems([
    // ...
    'two-factor-authentication' => MenuItem::make()
        ->icon('heroicon-o-lock-closed')
        ->label(__('Two-Factor Authentication'))
        ->url(fn(): string => TwoFactor::getUrl(['tenant' => auth()->user()->organization->getRouteKey()])),
])
```

### Forcing Two Factor Authentication

If you want to force users to enable Two Factor Authentication, you can add this to your `PanelProvider`:

```php
->plugins([
    TwoFactorAuthPlugin::make()->forced(),
])
```

> [!WARNING]
> When you're using the `forced` method, make sure to set the `multi_tenancy` option to `true` in the `filament-two-factor-auth.php` config file when you're using a multi-tenant setup. Otherwise, the forced setting will not work. We cannot check the tenant in the `PanelProvider` because the user is not authenticated yet.

#### Customizing the forced message

If you want to customize the forced message, you can publish the language file:

```bash
php artisan vendor:publish --tag="filament-two-factor-auth-translations"
```

Then you can customize the message in the `lang/vendor/filament-two-factor-auth/en.json` file. You should change the following keys:

```json
{
    "Your administrator requires you to enable two-factor authentication.": "Your custom message here.",
    "Two-Factor Authentication mandatory": "Your custom title here."
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Baspa](https://github.com/vormkracht10)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
