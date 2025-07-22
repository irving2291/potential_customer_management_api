<?php

namespace App\RequestInformation\Infrastructure\Persistence;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "request_note")]
class DoctrineRequestNoteEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: DoctrineRequestInformationEntity::class)]
    #[ORM\JoinColumn(name: "request_information_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private DoctrineRequestInformationEntity $requestInformation;

    #[ORM\Column(type: "string", length: 512)]
    private string $text;

    #[ORM\Column(type: "string", length: 80)]
    private string $createdBy;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct(
        string $id,
        DoctrineRequestInformationEntity $requestInformation,
        string $text,
        string $createdBy,
        \DateTimeImmutable $createdAt,
    ) {
        $this->id = $id;
        $this->requestInformation = $requestInformation;
        $this->text = $text;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
    }

    // GETTERS
    public function getId(): string { return $this->id; }
    public function getText(): string { return $this->text; }
    public function getCreatedBy(): string { return $this->createdBy; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getRequestInformation(): DoctrineRequestInformationEntity
    {
        return $this->requestInformation;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    // SETTERS
    public function setRequestInformation(DoctrineRequestInformationEntity $requestInformation): self
    {
        $this->requestInformation = $requestInformation;
        return $this;
    }

    public function setText(string $text): self { $this->text = $text; return $this; }

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
