<?php

namespace App\Quotation\Infrastructure\Persistence;

use Doctrine\ORM\Mapping as ORM;
use App\RequestInformation\Infrastructure\Persistence\DoctrineRequestInformationEntity;

#[ORM\Entity]
#[ORM\Table(name: "quotation")]
class DoctrineQuotationEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: DoctrineRequestInformationEntity::class)]
    #[ORM\JoinColumn(name: "request_information_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private DoctrineRequestInformationEntity $requestInformation;

    #[ORM\Column(type: "json")]
    private array $details; // Array de objetos QuotationDetail serializados

    #[ORM\Column(type: "string", length: 16)]
    private string $status;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    // ...Constructor, getters y setters...
    // --- GETTERS ---
    public function getId(): string
    {
        return $this->id;
    }
    public function getRequestInformation(): DoctrineRequestInformationEntity
    {
        return $this->requestInformation;
    }
    public function getDetails(): array
    {
        return $this->details;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    // --- SETTERS ---
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }
    public function setRequestInformation(DoctrineRequestInformationEntity $requestInformation): self
    {
        $this->requestInformation = $requestInformation;
        return $this;
    }
    public function setDetails(array $details): self
    {
        $this->details = $details;
        return $this;
    }
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
    public function setDeletedAt(?\DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }
}
