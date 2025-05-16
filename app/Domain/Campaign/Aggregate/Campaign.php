<?php

declare(strict_types=1);

namespace App\Domain\Campaign\Aggregate;

use App\Domain\Campaign\Event\CampaignApproved;
use App\Domain\Campaign\Event\CampaignCreated;
use App\Domain\Campaign\Event\CampaignFundsAdded; // You'd create this event
use App\Domain\Campaign\Event\CampaignRejected;   // You'd create this event
use App\Domain\Campaign\ValueObject\CampaignId;
use App\Domain\Campaign\ValueObject\CampaignStatus;
use App\Domain\Donation\ValueObject\DonationId;
use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Shared\Event\RaisesDomainEvents;
use App\Domain\Shared\ValueObject\Money;
use DateTimeImmutable;
use DomainException; // Standard PHP exception

class Campaign // This is an Aggregate Root
{
    use RaisesDomainEvents;

    private CampaignId $id;
    private EmployeeId $creatorId;
    private string $title;
    private string $description;
    private Money $goalAmount;
    private Money $currentAmount;
    private DateTimeImmutable $startDate;
    private DateTimeImmutable $endDate;
    private CampaignStatus $status;
    private ?DateTimeImmutable $approvedAt = null;
    private ?EmployeeId $approvedBy = null;
    private ?string $rejectionReason = null;

    // Private constructor to enforce creation via static factory method
    private function __construct(
        CampaignId $id,
        EmployeeId $creatorId,
        string $title,
        string $description,
        Money $goalAmount,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ) {
        $this->id = $id;
        $this->creatorId = $creatorId;
        $this->title = $title;
        $this->description = $description;
        $this->goalAmount = $goalAmount;
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        if ($startDate >= $endDate) {
            throw new DomainException("Start date must be before end date.");
        }
        if ($goalAmount->isZero() || $goalAmount->getAmountInCents() < 0) {
            throw new DomainException("Goal amount must be positive.");
        }

        $this->currentAmount = new Money(0, $goalAmount->getCurrency());
        $this->status = CampaignStatus::pending();

        $this->recordThat(new CampaignCreated(
            $this->id,
            $this->creatorId,
            $this->title,
            $this->goalAmount,
            $this->startDate,
            $this->endDate
        ));
    }

    public static function create(
        CampaignId $id,
        EmployeeId $creatorId,
        string $title,
        string $description,
        Money $goalAmount,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): self {
        return new self($id, $creatorId, $title, $description, $goalAmount, $startDate, $endDate);
    }

    public function getId(): CampaignId
    {
        return $this->id;
    }

    public function getCreatorId(): EmployeeId
    {
        return $this->creatorId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getGoalAmount(): Money
    {
        return $this->goalAmount;
    }

    public function getCurrentAmount(): Money
    {
        return $this->currentAmount;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getStatus(): CampaignStatus
    {
        return $this->status;
    }

    public function getApprovedAt(): ?DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function getApprovedBy(): ?EmployeeId
    {
        return $this->approvedBy;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function approve(EmployeeId $approvedByAdminId): void
    {
        if (!$this->status->equals(CampaignStatus::pending())) {
            throw new DomainException("Campaign cannot be approved. Current status: {$this->status}");
        }
        $this->status = CampaignStatus::approved();
        $this->approvedAt = new DateTimeImmutable();
        $this->approvedBy = $approvedByAdminId;
        $this->rejectionReason = null;

        $this->recordThat(new CampaignApproved($this->id, $approvedByAdminId));
        $this->updateStatusBasedOnDate(); // Check if it should become active
    }

    public function reject(EmployeeId $rejectedByAdminId, ?string $reason = null): void
    {
        if (!$this->status->equals(CampaignStatus::pending())) {
            throw new DomainException("Campaign cannot be rejected. Current status: {$this->status}");
        }
        $this->status = CampaignStatus::rejected();
        $this->rejectionReason = $reason;
        $this->approvedAt = null;
        $this->approvedBy = null;

        // You'd create and record CampaignRejected event here
        // $this->recordThat(new CampaignRejected($this->id, $rejectedByAdminId, $reason));
    }

    public function addFunds(Money $amount, DonationId $donationId): void
    {
        if (!$this->status->isOneOf([CampaignStatus::approved(), CampaignStatus::active()])) {
            throw new DomainException("Cannot add funds to a campaign that is not approved or active. Status: {$this->status}");
        }
        if ($this->endDate < new DateTimeImmutable()) {
            throw new DomainException("Cannot add funds to an ended campaign.");
        }
        if ($amount->getCurrency() !== $this->goalAmount->getCurrency()) {
            throw new DomainException("Donation currency mismatch.");
        }
        if ($amount->getAmountInCents() <= 0) {
            throw new DomainException("Donation amount must be positive.");
        }

        $this->currentAmount = $this->currentAmount->add($amount);

        // You'd create and record CampaignFundsAdded event here
        // $this->recordThat(new CampaignFundsAdded($this->id, $donationId, $amount, $this->currentAmount));

        $this->updateStatusBasedOnDate(); // Check if completed or still active
    }

    public function updateDetails(
        string $newTitle,
        string $newDescription,
        Money $newGoalAmount,
        DateTimeImmutable $newStartDate,
        DateTimeImmutable $newEndDate
    ): void {
        // Only allow updates if pending, or specific admin override rules
        if (!$this->status->equals(CampaignStatus::pending())) {
            // Or if user is admin and campaign is not yet active with donations
            throw new DomainException("Campaign details can only be updated when pending. Status: {$this->status}");
        }
        if ($newStartDate >= $newEndDate) {
            throw new DomainException("New start date must be before new end date.");
        }
        if ($newGoalAmount->isZero() || $newGoalAmount->getAmountInCents() < 0) {
            throw new DomainException("New goal amount must be positive.");
        }
        // Potentially check if currency changed and handle implications

        $this->title = $newTitle;
        $this->description = $newDescription;
        $this->goalAmount = $newGoalAmount;
        $this->startDate = $newStartDate;
        $this->endDate = $newEndDate;

        // Record CampaignDetailsUpdated event
    }


    public function updateStatusBasedOnDate(): void
    {
        $now = new DateTimeImmutable();

        if ($this->status->equals(CampaignStatus::approved()) && $now >= $this->startDate && $now <= $this->endDate) {
            $this->status = CampaignStatus::active();
            // Record CampaignActivated event
        }

        if ($this->status->isOneOf([CampaignStatus::approved(), CampaignStatus::active()])) {
            if ($this->currentAmount->isGreaterThanOrEqual($this->goalAmount)) {
                $this->status = CampaignStatus::completed();
                // Record CampaignGoalReached or CampaignCompleted event
            } elseif ($now > $this->endDate) {
                $this->status = CampaignStatus::completed(); // Or a different status like 'Expired'
                // Record CampaignEnded or CampaignCompleted event
            }
        }
    }

    // Method for persistence layer to reconstruct the object
    public static function reconstitute(
        CampaignId $id,
        EmployeeId $creatorId,
        string $title,
        string $description,
        Money $goalAmount,
        Money $currentAmount,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        CampaignStatus $status,
        ?DateTimeImmutable $approvedAt,
        ?EmployeeId $approvedBy,
        ?string $rejectionReason
    ): self {
        $campaign = new self(
            $id,
            $creatorId,
            $title,
            $description,
            $goalAmount,
            $startDate,
            $endDate
        );
        // Override initial state with persisted state
        $campaign->currentAmount = $currentAmount;
        $campaign->status = $status;
        $campaign->approvedAt = $approvedAt;
        $campaign->approvedBy = $approvedBy;
        $campaign->rejectionReason = $rejectionReason;
        $campaign->domainEvents = []; // Clear events from constructor

        return $campaign;
    }
}