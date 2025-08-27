<?php

namespace App\Assignee\Domain\Aggregate;

use App\Common\Domain\DomainEventRecorder;

class Assignee
{
    use DomainEventRecorder;

    private string $id;
    private string $firstName;
    private string $lastName;
    private string $email;
    private string $phone;
    private string $avatar;
    private bool $active;
    private string $role;
    private string $department;
    private string $organizationId;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $avatar,
        bool $active,
        string $role,
        string $department,
        string $organizationId
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->avatar = $avatar;
        $this->active = $active;
        $this->role = $role;
        $this->department = $department;
        $this->organizationId = $organizationId;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getDepartment(): string
    {
        return $this->department;
    }

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateInfo(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $role,
        string $department
    ): void {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->role = $role;
        $this->department = $department;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->active = true;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->active = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateAvatar(string $avatar): void
    {
        $this->avatar = $avatar;
        $this->updatedAt = new \DateTimeImmutable();
    }
}