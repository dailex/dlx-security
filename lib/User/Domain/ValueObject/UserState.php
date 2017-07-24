<?php

namespace Dlx\Security\User\Domain\ValueObject;

use Assert\Assertion;
use Daikon\Entity\ValueObject\ValueObjectInterface;

final class UserState implements ValueObjectInterface
{
    public const INITIAL = 'unverified';

    private const NIL = '';

    private const STATES = [
        'unverified',
        'verified',
        'activated',
        'deactivated',
        'deleted'
    ];

    private $state;

    public static function fromNative($nativeValue): ValueObjectInterface
    {
        Assertion::nullOrInArray($nativeValue, self::STATES);
        return $nativeValue ? new self($nativeValue) : self::makeEmpty();
    }

    public function toNative()
    {
        return $this->state;
    }

    public static function makeEmpty(): ValueObjectInterface
    {
        return new self(self::NIL);
    }

    public function equals(ValueObjectInterface $otherValue): bool
    {
        Assertion::isInstanceOf($otherValue, UserState::class);
        return $this->toNative() === $otherValue->toNative();
    }

    public function isInitial(): bool
    {
        return $this->state === self::INITIAL;
    }

    public function isEmpty(): bool
    {
        return $this->state === self::NIL;
    }

    public function __toString(): string
    {
        return $this->toNative();
    }

    private function __construct(string $state)
    {
        $this->state = $state;
    }
}
