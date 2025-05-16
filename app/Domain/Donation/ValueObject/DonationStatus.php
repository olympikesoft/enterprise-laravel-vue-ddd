<?php

declare(strict_types=1);

namespace App\Domain\Donation\ValueObject;

use InvalidArgumentException;

final class DonationStatus
{
    public const PENDING = 'pending'; // Payment initiated, awaiting confirmation
    public const SUCCEEDED = 'succeeded'; // Payment successful
    public const FAILED = 'failed'; // Payment failed
    public const REFUNDED = 'refunded';

    private const VALID_STATUSES = [
        self::PENDING,
        self::SUCCEEDED,
        self::FAILED,
        self::REFUNDED,
    ];

    private readonly string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid donation status: {$value}");
        }
        $this->value = $value;
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function succeeded(): self
    {
        return new self(self::SUCCEEDED);
    }

    public static function failed(): self
    {
        return new self(self::FAILED);
    }

    public static function refunded(): self
    {
        return new self(self::REFUNDED);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}