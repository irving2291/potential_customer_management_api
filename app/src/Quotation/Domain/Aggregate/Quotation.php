<?php

namespace App\Quotation\Domain\Aggregate;

use App\Quotation\Domain\ValueObject\QuotationDetail;
use App\Quotation\Domain\ValueObject\QuotationStatus;

class Quotation
{
    private ?string $id;
    private string $requestInformationId;
    private string $organizationId;
    /** @var QuotationDetail[] */
    private array $details;
    private QuotationStatus $status;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt = null;
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct(
        ?string $id,
        string $requestInformationId,
        string $organizationId,
        array $details,
        ?QuotationStatus $status,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt = null,
        ?\DateTimeImmutable $deletedAt = null
    ) {
        $this->id = $id;
        $this->requestInformationId = $requestInformationId;
        $this->organizationId = $organizationId;
        $this->details = $details;
        $this->status = $status ?? QuotationStatus::CREATING;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }

    // --- GETTERS & SETTERS ---
    public function getId(): ?string { return $this->id; }
    public function getRequestInformationId(): string { return $this->requestInformationId; }
    /** @return QuotationDetail[] */
    public function getDetails(): array { return $this->details; }
    public function getStatus(): QuotationStatus { return $this->status; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function getDeletedAt(): ?\DateTimeImmutable { return $this->deletedAt; }

    public function getOrganizationId(): string { return $this->organizationId; }

    public function setStatus(QuotationStatus $status): self
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function addDetail(QuotationDetail $detail): self
    {
        $this->details[] = $detail;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function removeDetail(int $index): self
    {
        unset($this->details[$index]);
        $this->details = array_values($this->details);
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function editDetail(int $index, QuotationDetail $detail): self
    {
        if (isset($this->details[$index])) {
            $this->details[$index] = $detail;
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function markDeleted(): self
    {
        $this->deletedAt = new \DateTimeImmutable();
        return $this;
    }

    public function setOrganizationId(string $organizationId): self { $this->organizationId = $organizationId; return $this; }
}
