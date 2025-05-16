<?php

declare(strict_types=1);

namespace App\Domain\Employee\Aggregate;

use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Shared\Event\RaisesDomainEvents; // If employees raise events
use DomainException;

class Employee // This is an Aggregate Root, representing a User/Admin
{
    // use RaisesDomainEvents; // Uncomment if employees raise domain events

    private EmployeeId $id;
    private string $name;
    private string $email;
    private string $hashedPassword; // Store hashed password
    private bool $isAdmin;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $emailVerifiedAt = null;


    private function __construct(
        EmployeeId $id,
        string $name,
        string $email,
        string $hashedPassword,
        bool $isAdmin = false
    ) {
        if (empty(trim($name))) {
            throw new DomainException("Employee name cannot be empty.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException("Invalid email format for employee.");
        }

        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->hashedPassword = $hashedPassword;
        $this->isAdmin = $isAdmin;
        $this->createdAt = new DateTimeImmutable();

        // Record EmployeeRegistered event if needed
        // $this->recordThat(new EmployeeRegistered($this->id, $this->name, $this->email, $this->isAdmin));
    }

    public static function register(
        EmployeeId $id,
        string $name,
        string $email,
        string $hashedPassword, // Password should be hashed by an application service before passing here
        bool $isAdmin = false
    ): self {
        return new self($id, $name, $email, $hashedPassword, $isAdmin);
    }

    public function getId(): EmployeeId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getHashedPassword(): string
    {
        return $this->hashedPassword;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getEmailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function verifyEmail(): void
    {
        if ($this->emailVerifiedAt !== null) {
            throw new DomainException("Email is already verified.");
        }
        $this->emailVerifiedAt = new DateTimeImmutable();
        // Record EmailVerified event
    }

    public function changePassword(string $newHashedPassword): void
    {
        $this->hashedPassword = $newHashedPassword;
        // Record PasswordChanged event
    }

    public function updateProfile(string $newName, string $newEmail): void
    {
        if (empty(trim($newName))) {
            throw new DomainException("Employee name cannot be empty.");
        }
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException("Invalid email format for employee.");
        }

        $emailChanged = $this->email !== $newEmail;
        $this->name = $newName;
        $this->email = $newEmail;

        if ($emailChanged) {
            $this->emailVerifiedAt = null; // Require re-verification if email changes
            // Record EmailChanged event, ProfileUpdated event
        } else {
            // Record ProfileUpdated event
        }
    }

    public function promoteToAdmin(): void
    {
        if ($this->isAdmin) {
            return; // Already admin
        }
        $this->isAdmin = true;
        // Record EmployeePromotedToAdmin event
    }

    public function demoteFromAdmin(): void
    {
        if (!$this->isAdmin) {
            return; // Not an admin
        }
        $this->isAdmin = false;
        // Record EmployeeDemotedFromAdmin event
    }

    // Method for persistence layer to reconstruct the object
    public static function reconstitute(
        EmployeeId $id,
        string $name,
        string $email,
        string $hashedPassword,
        bool $isAdmin,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $emailVerifiedAt
    ): self {
        $employee = new self($id, $name, $email, $hashedPassword, $isAdmin);
        // Override initial state
        $employee->createdAt = $createdAt;
        $employee->emailVerifiedAt = $emailVerifiedAt;
        // $employee->domainEvents = []; // Clear events from constructor if using RaisesDomainEvents trait

        return $employee;
    }
}