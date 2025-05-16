<?php

declare(strict_types=1);

namespace App\Domain\Campaign\ValueObject;

use InvalidArgumentException;

final class CampaignStatus
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const ACTIVE = 'active'; // Approved and within start/end dates
    public const COMPLETED = 'completed'; // Goal reached or end date passed
    public const CANCELLED = 'cancelled';

    private const VALID_STATUSES = [
        self::PENDING,
        self::APPROVED,
        self::REJECTED,
        self::ACTIVE,
        self::COMPLETED,
        self::CANCELLED,
    ];

    private readonly string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid campaign status: {$value}");
        }
        $this->value = $value;
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function approved(): self
    {
        return new self(self::APPROVED);
    }

    public static function rejected(): self
    {
        return new self(self::REJECTED);
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
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

    public function isOneOf(array $statuses): bool
    {
        foreach ($statuses as $status) {
            if ($this->value === $status->getValue()) {
                return true;
            }
        }
        return false;
    }
}