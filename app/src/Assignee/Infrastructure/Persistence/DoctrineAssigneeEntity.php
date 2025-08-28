<?php

namespace App\Assignee\Infrastructure\Persistence;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'assignees')]
class DoctrineAssigneeEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 100)]
    private string $lastName;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phone;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $avatar;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    #[ORM\Column(type: 'string', length: 100)]
    private string $role;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $department;

    #[ORM\Column(type: 'string', length: 36)]
    private string $organizationId;

    #[ORM\Column(type: 'string', length: 36)]
    private string $userId;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $firstName,
        string $lastName,
        string $email,
        ?string $phone,
        ?string $avatar,
        bool $active,
        string $role,
        ?string $department,
        string $organizationId,
        string $userId,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt = null
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
        $this->userId = $userId;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getAvatar(): ?string
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

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function setDepartment(string $department): void
    {
        $this->department = $department;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}