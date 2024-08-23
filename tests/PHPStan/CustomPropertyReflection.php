<?php

namespace Vormkracht10\TwoFactorAuth\Tests\PHPStan;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class CustomPropertyReflection implements PropertyReflection
{
    private ClassReflection $declaringClass;

    private Type $type;

    public function __construct(ClassReflection $declaringClass, ?Type $type = null)
    {
        $this->declaringClass = $declaringClass;
        $this->type = $type ?? new UnionType([
            new ObjectType('Carbon\Carbon'),
            new NullType,
        ]);
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->declaringClass;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function isStatic(): bool
    {
        return false;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getDeprecatedDescription(): ?string
    {
        return null;
    }

    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getDocComment(): ?string
    {
        return null;
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getReadableType(): Type
    {
        return $this->type;
    }

    public function getWritableType(): Type
    {
        return $this->type;
    }

    public function canChangeTypeAfterAssignment(): bool
    {
        return false;
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function isWritable(): bool
    {
        return true;
    }
}
