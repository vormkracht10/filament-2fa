# Filament Two Factor Auth 

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vormkracht10/filament-two-factor-auth.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/filament-two-factor-auth)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/vormkracht10/filament-two-factor-auth/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/vormkracht10/filament-two-factor-auth/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/vormkracht10/filament-two-factor-auth/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/vormkracht10/filament-two-factor-auth/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/vormkracht10/filament-two-factor-auth.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/filament-two-factor-auth)



This package helps you integrate Laravel Fortify with ease in your Filament apps. 

## Installation

You can install the package via composer:

```bash
composer require vormkracht10/filament-two-factor-auth
```

You can easily install the plugin by running the following command:

```bash
 php artisan filament-two-factor-auth:install
```

If you don't have [Laravel Fortify](https://laravel.com/docs/11.x/fortify) installed yet, you can install it by running the following commands:

```bash
composer require laravel/fortify

php artisan fortify:install

php artisan migrate
```

Then add the plugin to your `PanelProvider`:

```php
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

## Usage

```php
$twoFactorAuth = new Vormkracht10\TwoFactorAuth();
echo $twoFactorAuth->echoPhrase('Hello, Vormkracht10!');
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

- [Baspa](https://github.com/vormkracht10)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
