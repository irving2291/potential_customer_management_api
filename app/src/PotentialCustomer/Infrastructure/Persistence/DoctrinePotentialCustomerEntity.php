<?php

// src/PotentialCustomer/Infrastructure/Persistence/DoctrinePotentialCustomerEntity.php
namespace App\PotentialCustomer\Infrastructure\Persistence;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "potential_customer")]
class DoctrinePotentialCustomerEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 36)]
    private string $id;

    #[ORM\Column(type: "string", length: 20)]
    private string $type;

    #[ORM\Column(type: "string", length: 20)]
    private string $status;

    #[ORM\Column(type: "string", length: 80, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(type: "string", length: 80, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $companyName = null;

    #[ORM\OneToMany(
        mappedBy: "potentialCustomer",
        targetEntity: DoctrineEmailEntity::class,
        cascade: ["persist", "remove"],
        orphanRemoval: true
    )]
    private Collection $emails;

    #[ORM\Column(type: "string", length: 32, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: "string", length: 64, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: "string", length: 64, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $industry = null;

    #[ORM\Column(type: "json")]
    private array $tags = [];

    #[ORM\Column(type: "string", length: 10)]
    private string $priority;

    #[ORM\Column(type: "string", length: 36, nullable: true)]
    private ?string $assignedTo = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $assignedToName = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private float $totalValue = 0.0;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private float $potentialValue = 0.0;

    #[ORM\Column(type: "string", length: 10, nullable: true)]
    private ?string $lastContactDate = null;

    #[ORM\Column(type: "integer")]
    private int $requestsCount = 0;

    #[ORM\Column(type: "integer")]
    private int $quotationsCount = 0;

    #[ORM\Column(type: "integer")]
    private int $conversationsCount = 0;

    #[ORM\Column(type: "string", length: 36)]
    private string $organizationId;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $convertedAt = null;

    public function __construct()
    {
        $this->emails = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters
    public function getId(): string { return $this->id; }
    public function getType(): string { return $this->type; }
    public function getStatus(): string { return $this->status; }
    public function getFirstName(): ?string { return $this->firstName; }
    public function getLastName(): ?string { return $this->lastName; }
    public function getCompanyName(): ?string { return $this->companyName; }
    public function getPhone(): ?string { return $this->phone; }
    public function getAddress(): ?string { return $this->address; }
    public function getCity(): ?string { return $this->city; }
    public function getCountry(): ?string { return $this->country; }
    public function getWebsite(): ?string { return $this->website; }
    public function getIndustry(): ?string { return $this->industry; }
    public function getTags(): array { return $this->tags; }
    public function getPriority(): string { return $this->priority; }
    public function getAssignedTo(): ?string { return $this->assignedTo; }
    public function getAssignedToName(): ?string { return $this->assignedToName; }
    public function getTotalValue(): float { return $this->totalValue; }
    public function getPotentialValue(): float { return $this->potentialValue; }
    public function getLastContactDate(): ?string { return $this->lastContactDate; }
    public function getRequestsCount(): int { return $this->requestsCount; }
    public function getQuotationsCount(): int { return $this->quotationsCount; }
    public function getConversationsCount(): int { return $this->conversationsCount; }
    public function getOrganizationId(): string { return $this->organizationId; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function getConvertedAt(): ?\DateTimeImmutable { return $this->convertedAt; }

    // Setters
    public function setId(string $id): self { $this->id = $id; return $this; }
    public function setType(string $type): self { $this->type = $type; return $this; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function setFirstName(?string $firstName): self { $this->firstName = $firstName; return $this; }
    public function setLastName(?string $lastName): self { $this->lastName = $lastName; return $this; }
    public function setCompanyName(?string $companyName): self { $this->companyName = $companyName; return $this; }
    public function setPhone(?string $phone): self { $this->phone = $phone; return $this; }
    public function setAddress(?string $address): self { $this->address = $address; return $this; }
    public function setCity(?string $city): self { $this->city = $city; return $this; }
    public function setCountry(?string $country): self { $this->country = $country; return $this; }
    public function setWebsite(?string $website): self { $this->website = $website; return $this; }
    public function setIndustry(?string $industry): self { $this->industry = $industry; return $this; }
    public function setTags(array $tags): self { $this->tags = $tags; return $this; }
    public function setPriority(string $priority): self { $this->priority = $priority; return $this; }
    public function setAssignedTo(?string $assignedTo): self { $this->assignedTo = $assignedTo; return $this; }
    public function setAssignedToName(?string $assignedToName): self { $this->assignedToName = $assignedToName; return $this; }
    public function setTotalValue(float $totalValue): self { $this->totalValue = $totalValue; return $this; }
    public function setPotentialValue(float $potentialValue): self { $this->potentialValue = $potentialValue; return $this; }
    public function setLastContactDate(?string $lastContactDate): self { $this->lastContactDate = $lastContactDate; return $this; }
    public function setRequestsCount(int $requestsCount): self { $this->requestsCount = $requestsCount; return $this; }
    public function setQuotationsCount(int $quotationsCount): self { $this->quotationsCount = $quotationsCount; return $this; }
    public function setConversationsCount(int $conversationsCount): self { $this->conversationsCount = $conversationsCount; return $this; }
    public function setOrganizationId(string $organizationId): self { $this->organizationId = $organizationId; return $this; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }
    public function setConvertedAt(?\DateTimeImmutable $convertedAt): self { $this->convertedAt = $convertedAt; return $this; }

    /** @return Collection|DoctrineEmailEntity[] */
    public function getEmails(): Collection { return $this->emails; }

    public function addEmail(DoctrineEmailEntity $email): self
    {
        if (!$this->emails->contains($email)) {
            $this->emails[] = $email;
            $email->setPotentialCustomer($this);
        }
        return $this;
    }
}
