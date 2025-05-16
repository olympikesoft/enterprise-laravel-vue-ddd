<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use InvalidArgumentException;
use JsonSerializable;

final class Money implements JsonSerializable
{
    private readonly int $amount; // Store amount in cents to avoid floating point issues
    private readonly string $currency; // e.g., "USD", "EUR"

    public function __construct(int $amountInCents, string $currency)
    {
        if (empty($currency) || strlen($currency) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-letter code.');
        }
        $this->amount = $amountInCents;
        $this->currency = strtoupper($currency);
    }

    public static function fromFloat(float $amount, string $currency): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public function getAmountInCents(): int
    {
        return $this->amount;
    }

    public function getAmountAsFloat(): float
    {
        return $this->amount / 100;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot add money with different currencies.');
        }
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot subtract money with different currencies.');
        }
        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(float $multiplier): self
    {
        return new self((int) round($this->amount * $multiplier), $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function isGreaterThan(self $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot compare money with different currencies.');
        }
        return $this->amount > $other->amount;
    }

    public function isGreaterThanOrEqual(self $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot compare money with different currencies.');
        }
        return $this->amount >= $other->amount;
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function __toString(): string
    {
        return sprintf('%.2f %s', $this->getAmountAsFloat(), $this->currency);
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->getAmountAsFloat(),
            'amount_in_cents' => $this->amount,
            'currency' => $this->currency,
        ];
    }
}