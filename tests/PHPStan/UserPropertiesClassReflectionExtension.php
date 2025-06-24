<?php

namespace Backstage\TwoFactorAuth\Tests\PHPStan;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;

class UserPropertiesClassReflectionExtension implements PropertiesClassReflectionExtension
{
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        return $classReflection->getName() === 'Illuminate\Foundation\Auth\User' && $propertyName === 'two_factor_confirmed_at';
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return new CustomPropertyReflection($classReflection);
    }
}
