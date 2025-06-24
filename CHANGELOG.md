# Changelog

All notable changes to `filament-2fa` will be documented in this file.

## v3.0.0 - 2025-06-24

### Breaking changes

- Changed namespace to `Backstage`

### What's Changed

* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/backstagephp/filament-2fa/pull/81
* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot in https://github.com/backstagephp/filament-2fa/pull/82
* Fixed: infinity redirect loop when panel path is base url by @Michel-Verhoeven in https://github.com/backstagephp/filament-2fa/pull/83
* Fixing translation override by @ccharz in https://github.com/backstagephp/filament-2fa/pull/88
* Add German translations to language files by @thyseus in https://github.com/backstagephp/filament-2fa/pull/89
* Bump dependabot/fetch-metadata from 2.3.0 to 2.4.0 by @dependabot in https://github.com/backstagephp/filament-2fa/pull/90
* Fix missing of loading button when Login by @informagenie in https://github.com/backstagephp/filament-2fa/pull/91
* Update README by @Baspa in https://github.com/backstagephp/filament-2fa/pull/92
* Update TwoFactor.php by @SinaMoradi9571 in https://github.com/backstagephp/filament-2fa/pull/94
* Transfer repository to Backstage by @Baspa in https://github.com/backstagephp/filament-2fa/pull/97

### New Contributors

* @Michel-Verhoeven made their first contribution in https://github.com/backstagephp/filament-2fa/pull/83
* @ccharz made their first contribution in https://github.com/backstagephp/filament-2fa/pull/88
* @thyseus made their first contribution in https://github.com/backstagephp/filament-2fa/pull/89
* @informagenie made their first contribution in https://github.com/backstagephp/filament-2fa/pull/91
* @SinaMoradi9571 made their first contribution in https://github.com/backstagephp/filament-2fa/pull/94

**Full Changelog**: https://github.com/backstagephp/filament-2fa/compare/v2.1.0...v3.0.0

## v2.1.0 - 2025-01-21

### What's Changed

* Fix config publish after package has been renamed by @rrelmy in https://github.com/backstagephp/filament-2fa/pull/54
* Fix white background in darkmode by @rrelmy in https://github.com/backstagephp/filament-2fa/pull/55
* Add white border around QRCode to improve legibility in darkmode by @rrelmy in https://github.com/backstagephp/filament-2fa/pull/56
* feat: add validation for empty options while setting two factor auth by @lucascnunes in https://github.com/backstagephp/filament-2fa/pull/52
* Fix email 2FA users_email_unique SQL error #61 by @cawecoy in https://github.com/backstagephp/filament-2fa/pull/62
* Fix password reset link by @Baspa in https://github.com/backstagephp/filament-2fa/pull/67
* Theme colors not being used by @Baspa in https://github.com/backstagephp/filament-2fa/pull/68
* Fix primary colors bug by @arduinomaster22 in https://github.com/backstagephp/filament-2fa/pull/70
* Optionally force not showing the user menu item by @Baspa in https://github.com/backstagephp/filament-2fa/pull/74
* [Fix] Width of 2FA auth page by @Baspa in https://github.com/backstagephp/filament-2fa/pull/77
* [Fix] Set default two_factor_type to email when not set by @Baspa in https://github.com/backstagephp/filament-2fa/pull/78
* [Feature] Show error message when OTP code is invalid by @Baspa in https://github.com/backstagephp/filament-2fa/pull/80

### New Contributors

* @rrelmy made their first contribution in https://github.com/backstagephp/filament-2fa/pull/54
* @lucascnunes made their first contribution in https://github.com/backstagephp/filament-2fa/pull/52
* @cawecoy made their first contribution in https://github.com/backstagephp/filament-2fa/pull/62
* @arduinomaster22 made their first contribution in https://github.com/backstagephp/filament-2fa/pull/70

**Full Changelog**: https://github.com/backstagephp/filament-2fa/compare/v2.0.0...v2.1.0

## Merged two factor code inputs (2fa code and recovery code) - 2024-10-22

Breaking changes:

- Added hidden recovery code input to merge 2fa and recovery code input to one input (better UX)
- Changed vendor namespace of package from `filament-two-factor-auth` to `filament-2fa`

How to upgrade:

- Change `backstage/filament-two-factor-auth` to `backstage/filament-2fa` and require `2.0.0` in `composer.json` and run `composer update`
- Rename `config/filament-two-factor-auth.php` to `config/filament-2fa.php` (when config is published)
- Rename `views/vendor/filament-two-factor-auth` to `views/vendor/filament-2fa` (when views are published)
- Make sure `vendor/filament-2fa/auth/login-two-factor.blade.php` contains a hidden input named `recovery_code`:

```html
<div style="display: none">
    <input type="text" id="recovery_code" wire:model="recovery_code" name="recovery_code" value="">
</div>```



```
## v1.7.0 - 2024-10-04

### What's Changed

* [Improvement] Remove unnecessary check on tenant for menu item by @Baspa in https://github.com/backstagephp/filament-2fa/pull/45
* Set two factor type for users that already enabled 2FA by @Baspa in https://github.com/backstagephp/filament-2fa/pull/49

**Full Changelog**: https://github.com/backstagephp/filament-2fa/compare/v1.6.0...v1.7.0

## v1.6.0 - 2024-09-20

### What's Changed

* [Bug fix] Undefined method 'via' by @Baspa in https://github.com/backstagephp/filament-2fa/pull/41
* [Bug fix] NPM error when building by @Baspa in https://github.com/backstagephp/filament-2fa/pull/42
* Update forced method to accept Closure type by @CodeWithDennis in https://github.com/backstagephp/filament-2fa/pull/43
* [Feature] Show user phone or email before sending OTP by @Baspa in https://github.com/backstagephp/filament-2fa/pull/39

**Full Changelog**: https://github.com/backstagephp/filament-2fa/compare/v1.5.0...v1.6.0

## v1.5.0 - 2024-09-18

### What's Changed

* [Fix] Add missing return type for custom middleware by @Baspa in https://github.com/backstagephp/filament-2fa/pull/37
* [Feature] improve successfully seting 2FA by @Baspa in https://github.com/backstagephp/filament-2fa/pull/38

**Full Changelog**: https://github.com/backstagephp/filament-2fa/compare/v1.4.0...v1.5.0

## v1.4.0 - 2024-09-15

### What's Changed

* Add `forced()` method and middleware by @CodeWithDennis in https://github.com/backstagephp/filament-2fa/pull/28

**Full Changelog**: https://github.com/backstagephp/filament-2fa/compare/v1.3.0...v1.4.0

## v1.3.0 - 2024-09-10

### What's Changed

* [Fix] Layouts by @Baspa in https://github.com/backstagephp/filament-2fa/pull/25
* [Feature] Improve documentation about sms services by @Baspa in https://github.com/backstagephp/filament-2fa/pull/29

**Full Changelog**: https://github.com/backstagephp/filament-2fa/compare/v1.2.0...v1.3.0

## v1.2.0 - 2024-08-28

### What's Changed

* Customizable notification email mailable by @CodeWithDennis in https://github.com/backstagephp/filament-2fa/pull/7
* PHPStan improvements by @Baspa in https://github.com/backstagephp/filament-2fa/pull/8
* [Bug] Fix asset registration in provider by @Baspa in https://github.com/backstagephp/filament-2fa/pull/12
* [Bug fix] Invalid payload by @Baspa in https://github.com/backstagephp/filament-2fa/pull/16
* Validate before pipeline to show error messages to users by @Baspa in https://github.com/backstagephp/filament-2fa/pull/18
* Use validate credentials before attempting to login by @Baspa in https://github.com/backstagephp/filament-2fa/pull/19
* [Bug fix] Resending otp code sometimes sends duplicates by @Baspa in https://github.com/backstagephp/filament-2fa/pull/20
* [Feature] Allow register page customisation and fix tenancy issue by @Baspa in https://github.com/backstagephp/filament-2fa/pull/22
* [Bug fix] OTP code should be required when challenging by @Baspa in https://github.com/backstagephp/filament-2fa/pull/23
* [Feature] Validate invalid OTP by @Baspa in https://github.com/backstagephp/filament-2fa/pull/24

**Full Changelog**: https://github.com/backstagephp/filament-2fa/compare/v1.1.0...v1.2.0

## v1.1.0 - 2024-08-23

### What's Changed

* [Fix] Redirecting to wrong panel after challenge by @Baspa in https://github.com/backstagephp/filament-2fa/pull/3
* [Bugfix] Form options showed ALL options instead of the configured options by @CodeWithDennis in https://github.com/backstagephp/filament-2fa/pull/5
* [Bugfix] Dark mode and small fixes by @CodeWithDennis in https://github.com/backstagephp/filament-2fa/pull/4
* Add missing dutch translations by @CodeWithDennis in https://github.com/backstagephp/filament-2fa/pull/6

### New Contributors

* @Baspa made their first contribution in https://github.com/backstagephp/filament-2fa/pull/3
* @CodeWithDennis made their first contribution in https://github.com/backstagephp/filament-2fa/pull/5

**Full Changelog**: https://github.com/backstagephp/filament-2fa/compare/v1.0.0...v1.1.0

## 1.0.0 - 202X-XX-XX

- initial release
