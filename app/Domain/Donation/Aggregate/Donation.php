<?php

declare(strict_types=1);

namespace App\Domain\Donation\Aggregate;

use App\Domain\Donation\Event\DonationInitiated;
use App\Domain\Donation\Event\DonationSucceeded;
use App\Domain\Donation\ValueObject\DonationStatus;
use App\Domain\Shared\Event\RaisesDomainEvents;
use App\Domain\Shared\ValueObject\Money;
use DateTimeImmutable;
use DomainException;

class Donation
{
    use RaisesDomainEvents;

    private int $id;
    private int $campaignId;
    private int $donorId;
    private Money $amount;
    private DonationStatus $status;
    private ?string $message;
    private DateTimeImmutable $createdAt;
    private ?string $transactionReference = null;
    private ?string $failureReason = null;

    private function __construct(
        int $id,
        int $campaignId,
        Money $amount,
        int $donorId,
        ?string $message
    ) {
        if ($amount->getAmountInCents() <= 0) {
            throw new DomainException("Donation amount must be positive.");
        }

        $this->id = $id;
        $this->campaignId = $campaignId;
        $this->amount = $amount;
        $this->donorId = $donorId;
        $this->message = $message;
        $this->status = DonationStatus::pending();
        $this->createdAt = new DateTimeImmutable();

        $this->recordThat(new DonationInitiated(
            $this->id,
            $this->campaignId,
            $this->amount,
            $this->donorId,
        ));
    }

    public static function make(
        int $id,
        int $campaignId,
        Money $amount,
        ?int $donorId,
        ?string $message
    ): self {
        return new self($id, $campaignId, $amount, $donorId, $message);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCampaignId(): int
    {
        return $this->campaignId;
    }

    public function getDonorId(): ?int
    {
        return $this->donorId;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getStatus(): DonationStatus
    {
        return $this->status;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getTransactionReference(): ?string
    {
        return $this->transactionReference;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function markAsSucceeded(string $transactionRef): void
    {
        if (!$this->status->equals(DonationStatus::pending())) {
            throw new DomainException("Donation cannot be marked as succeeded. Current status: {$this->status}");
        }
        $this->status = DonationStatus::succeeded();
        $this->transactionReference = $transactionRef;
        $this->failureReason = null;

        $this->recordThat(new DonationSucceeded($this->id, $this->campaignId, $this->amount, $transactionRef));
    }

    public function markAsFailed(string $reason): void
    {
        if (!$this->status->equals(DonationStatus::pending())) {
            throw new DomainException("Donation cannot be marked as failed. Current status: {$this->status}");
        }
        $this->status = DonationStatus::failed();
        $this->failureReason = $reason;
        $this->transactionReference = null;

    }

    // Method for persistence layer to reconstruct the object
    public static function reconstitute(
        int $id,
        int $campaignId,
        int $donorId,
        Money $amount,
        DonationStatus $status,
        ?string $message,
        DateTimeImmutable $createdAt,
        ?string $transactionReference,
        ?string $failureReason
    ): self {
        $donation = new self($id, $campaignId, $amount, $donorId, $message);
        $donation->status = $status;
        $donation->createdAt = $createdAt; // Keep original creation time
        $donation->transactionReference = $transactionReference;
        $donation->failureReason = $failureReason;
        $donation->domainEvents = []; // Clear events from constructor

        return $donation;
    }

    public static function reconstituteFromArray(array $data): self
    {
        $donation = new self(
            $data['id'],
            $data['campaignId'],
            $data['amount'],
            $data['donorId'],
            $data['message'] ?? null
        );
        return $donation;
    }
}
