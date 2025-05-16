<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use Ramsey\Uuid\Uuid; // A common library for UUIDs
use Ramsey\Uuid\UuidInterface;
use Stringable;
use InvalidArgumentException;

abstract class AbstractId implements Stringable
{
    protected readonly UuidInterface $value;

    final protected function __construct(UuidInterface $value)
    {
        $this->value = $value;
    }

    public static function generate(): static
    {
        return new static(Uuid::uuid4());
    }

    public static function fromString(string $uuidString): static
    {
        if (!Uuid::isValid($uuidString)) {
            throw new InvalidArgumentException("Invalid UUID string provided for " . static::class . ": {$uuidString}");
        }
        return new static(Uuid::fromString($uuidString));
    }

    public function toString(): string
    {
        return $this->value->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function equals(self $other): bool
    {
        return $this->value->equals($other->value) && static::class === $other::class;
    }
}