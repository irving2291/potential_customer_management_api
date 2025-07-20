<?php

namespace App\RequestInformation\Infrastructure\Persistence;

use Doctrine\ORM\Mapping as ORM;
use App\RequestInformation\Domain\ValueObject\RequestStatus;

#[ORM\Entity]
#[ORM\Table(name: "request_information")]
class DoctrineRequestInformationEntity
{
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(type: "string", length: 36)]
    private ?string $programInterestId = null;

    #[ORM\Column(type: "string", length: 36)]
    private ?string $leadOriginId = null;

    #[ORM\Column(enumType: RequestStatus::class)]
    private RequestStatus $status = RequestStatus::NEW;

    #[ORM\Column(type: "string", length: 80)]
    private string $firstName;

    #[ORM\Column(type: "string", length: 80)]
    private string $lastName;

    #[ORM\Column(type: "string", length: 255)]
    private string $email;

    #[ORM\Column(type: "string", length: 32)]
    private string $phone;

    #[ORM\Column(type: "string", length: 64)]
    private string $city;

    #[ORM\Column(type: "datetime_immutable", options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTime $deletedAt = null;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $lastUserUpdated = null;

    // --- GETTERS ---

    public function getStatus(): RequestStatus
    {
        return $this->status;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProgramInterestId(): ?string
    {
        return $this->programInterestId;
    }

    public function getLeadOriginId(): string
    {
        return $this->leadOriginId;
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

    public function getCity(): string
    {
        return $this->city;
    }

    // --- SETTERS ---
    public function setStatus(RequestStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setProgramInterestId(string $programInterestId): self
    {
        $this->programInterestId = $programInterestId;
        return $this;
    }

    public function setLeadOriginId(string $leadOriginId): self
    {
        $this->leadOriginId = $leadOriginId;
        return $this;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function getLastUserUpdated(): ?array
    {
        return $this->lastUserUpdated;
    }

    public function setLastUserUpdated(?array $lastUserUpdated): self
    {
        $this->lastUserUpdated = $lastUserUpdated;
        return $this;
    }

}
