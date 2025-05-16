<?php

declare(strict_types=1);

namespace App\Domain\Donation\Aggregate;

use App\Domain\Campaign\ValueObject\CampaignId;
use App\Domain\Donation\Event\DonationFailed; // You'd create this
use App\Domain\Donation\Event\DonationInitiated;
use App\Domain\Donation\Event\DonationSucceeded;
use App\Domain\Donation\ValueObject\DonationId;
use App\Domain\Donation\ValueObject\DonationStatus;
use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Shared\Event\RaisesDomainEvents;
use App\Domain\Shared\ValueObject\Money;
use DateTimeImmutable;
use DomainException;

class Donation // This is an Aggregate Root
{
    use RaisesDomainEvents;

    private DonationId $id;
    private CampaignId $campaignId;
    private ?EmployeeId $donorId; // Nullable for anonymous
    private ?string $donorName; // For anonymous or if user provides a different display name
    private Money $amount;
    private DonationStatus $status;
    private ?string $message;
    private DateTimeImmutable $createdAt;
    private ?string $transactionReference = null; // From payment gateway
    private ?string $failureReason = null;

    private function __construct(
        DonationId $id,
        CampaignId $campaignId,
        Money $amount,
        ?EmployeeId $donorId,
        ?string $donorName,
        ?string $message
    ) {
        if ($amount->getAmountInCents() <= 0) {
            throw new DomainException("Donation amount must be positive.");
        }
        if ($donorId === null && empty($donorName)) {
            throw new DomainException("Donor name is required for anonymous donations.");
        }

        $this->id = $id;
        $this->campaignId = $campaignId;
        $this->amount = $amount;
        $this->donorId = $donorId;
        $this->donorName = $donorName ?: ($donorId ? 'Registered User' : 'Anonymous'); // Default if not provided
        $this->message = $message;
        $this->status = DonationStatus::pending();
        $this->createdAt = new DateTimeImmutable();

        $this->recordThat(new DonationInitiated(
            $this->id,
            $this->campaignId,
            $this->amount,
            $this->donorId,
            $this->donorName
        ));
    }

    public static function make(
        DonationId $id,
        CampaignId $campaignId,
        Money $amount,
        ?EmployeeId $donorId,
        ?string $donorName,
        ?string $message
    ): self {
        return new self($id, $campaignId, $amount, $donorId, $donorName, $message);
    }

    public function getId(): DonationId
    {
        return $this->id;
    }

    public function getCampaignId(): CampaignId
    {
        return $this->campaignId;
    }

    public function getDonorId(): ?EmployeeId
    {
        return $this->donorId;
    }

    public function getDonorName(): ?string
    {
        return $this->donorName;
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

        // You'd create and record DonationFailed event here
        // $this->recordThat(new DonationFailed($this->id, $this->campaignId, $this->amount, $reason));
    }

    // Method for persistence layer to reconstruct the object
    public static function reconstitute(
        DonationId $id,
        CampaignId $campaignId,
        ?EmployeeId $donorId,
        ?string $donorName,
        Money $amount,
        DonationStatus $status,
        ?string $message,
        DateTimeImmutable $createdAt,
        ?string $transactionReference,
        ?string $failureReason
    ): self {
        $donation = new self($id, $campaignId, $amount, $donorId, $donorName, $message);
        // Override initial state
        $donation->status = $status;
        $donation->createdAt = $createdAt; // Keep original creation time
        $donation->transactionReference = $transactionReference;
        $donation->failureReason = $failureReason;
        $donation->domainEvents = []; // Clear events from constructor

        return $donation;
    }
}