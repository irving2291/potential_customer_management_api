<?php

namespace App\PotentialCustomer\Domain\Aggregate;

use App\PotentialCustomer\Domain\Entity\Email;

class PotentialCustomer
{
    private string $id;
    private string $type; // 'person' or 'company'
    private string $status; // 'prospect', 'client', 'inactive'
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $companyName = null;
    /** @var Email[] */
    private array $emails = [];
    private ?string $phone = null;
    private ?string $address = null;
    private ?string $city = null;
    private ?string $country = null;
    private ?string $website = null;
    private ?string $industry = null;
    private array $tags = [];
    private string $priority; // 'low', 'medium', 'high'
    private ?string $assignedTo = null;
    private ?string $assignedToName = null;
    private float $totalValue = 0.0;
    private float $potentialValue = 0.0;
    private ?string $lastContactDate = null;
    private int $requestsCount = 0;
    private int $quotationsCount = 0;
    private int $conversationsCount = 0;
    private string $organizationId;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt = null;
    private ?\DateTimeImmutable $convertedAt = null;

    public function __construct(
        string $id,
        string $type,
        string $organizationId,
        array $emails,
        string $phone,
        string $priority,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $companyName = null,
        ?string $city = null,
        ?string $assignedTo = null,
        ?string $assignedToName = null
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->status = 'prospect'; // Default status
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->companyName = $companyName;
        $this->emails = $emails;
        $this->phone = $phone;
        $this->city = $city;
        $this->priority = $priority;
        $this->assignedTo = $assignedTo;
        $this->assignedToName = $assignedToName;
        $this->organizationId = $organizationId;
        $this->createdAt = new \DateTimeImmutable();
    }

    // ---- GETTERS ----

    public function getId(): string { return $this->id; }
    public function getType(): string { return $this->type; }
    public function getStatus(): string { return $this->status; }
    public function getFirstName(): ?string { return $this->firstName; }
    public function getLastName(): ?string { return $this->lastName; }
    public function getCompanyName(): ?string { return $this->companyName; }
    public function getEmails(): array { return $this->emails; }
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

    // ---- SETTERS ----

    public function setType(string $type): void { $this->type = $type; $this->touch(); }
    public function setFirstName(?string $firstName): void { $this->firstName = $firstName; $this->touch(); }
    public function setLastName(?string $lastName): void { $this->lastName = $lastName; $this->touch(); }
    public function setCompanyName(?string $companyName): void { $this->companyName = $companyName; $this->touch(); }
    public function setEmails(array $emails): void { $this->emails = $emails; $this->touch(); }
    public function setPhone(?string $phone): void { $this->phone = $phone; $this->touch(); }
    public function setAddress(?string $address): void { $this->address = $address; $this->touch(); }
    public function setCity(?string $city): void { $this->city = $city; $this->touch(); }
    public function setCountry(?string $country): void { $this->country = $country; $this->touch(); }
    public function setWebsite(?string $website): void { $this->website = $website; $this->touch(); }
    public function setIndustry(?string $industry): void { $this->industry = $industry; $this->touch(); }
    public function setTags(array $tags): void { $this->tags = $tags; $this->touch(); }
    public function setPriority(string $priority): void { $this->priority = $priority; $this->touch(); }
    public function setAssignedTo(?string $assignedTo): void { $this->assignedTo = $assignedTo; $this->touch(); }
    public function setAssignedToName(?string $assignedToName): void { $this->assignedToName = $assignedToName; $this->touch(); }
    public function setTotalValue(float $totalValue): void { $this->totalValue = $totalValue; $this->touch(); }
    public function setPotentialValue(float $potentialValue): void { $this->potentialValue = $potentialValue; $this->touch(); }
    public function setLastContactDate(?string $lastContactDate): void { $this->lastContactDate = $lastContactDate; $this->touch(); }
    public function setRequestsCount(int $requestsCount): void { $this->requestsCount = $requestsCount; }
    public function setQuotationsCount(int $quotationsCount): void { $this->quotationsCount = $quotationsCount; }
    public function setConversationsCount(int $conversationsCount): void { $this->conversationsCount = $conversationsCount; }

    // ---- BUSINESS LOGIC ----

    public function convertToClient(): void
    {
        $this->status = 'client';
        $this->convertedAt = new \DateTimeImmutable();
        $this->touch();
    }

    public function markAsInactive(): void
    {
        $this->status = 'inactive';
        $this->touch();
    }

    public function reactivate(): void
    {
        $this->status = 'prospect';
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getPrimaryEmail(): ?string
    {
        if (empty($this->emails)) {
            return null;
        }
        return $this->emails[0]->getValue();
    }

    // ---- OTHER LOGIC ----

    public function addEmail(Email $email): void
    {
        foreach ($this->emails as $existing) {
            if ($existing->getValue() === $email->getValue()) {
                return; // Ya existe, no la agregues de nuevo
            }
        }
        $this->emails[] = $email;
    }

    public function hasEmail(string $email): bool
    {
        foreach ($this->emails as $existing) {
            if ($existing->getValue() === strtolower($email)) {
                return true;
            }
        }
        return false;
    }
}
