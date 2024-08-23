<?php

namespace Vormkracht10\TwoFactorAuth\Tests\PHPStan;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\ObjectType;

class UserPropertiesClassReflectionExtension implements PropertiesClassReflectionExtension
{
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        return $classReflection->getName() === 'Illuminate\Foundation\Auth\User' && $propertyName === 'two_factor_confirmed_at';
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        $type = new ObjectType('Carbon\Carbon');
        return new CustomPropertyReflection($classReflection, $type);
    }
}