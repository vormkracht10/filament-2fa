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

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-two-factor-auth-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-two-factor-auth-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-two-factor-auth-views"
```

This is the contents of the published config file:

```php
return [
];
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
