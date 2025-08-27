<?php

namespace App\Activation\Infrastructure\Persistence;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'activations')]
class DoctrineActivationEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'string', length: 50)]
    private string $type;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status;

    #[ORM\Column(type: 'string', length: 50)]
    private string $priority;

    #[ORM\Column(type: 'json')]
    private array $channels;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $targetAudience;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $scheduledFor;

    #[ORM\Column(type: 'string', length: 36)]
    private string $organizationId;

    #[ORM\Column(type: 'string', length: 36)]
    private string $createdBy;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $title,
        string $description,
        string $type,
        string $status,
        string $priority,
        array $channels,
        string $organizationId,
        string $createdBy,
        \DateTimeImmutable $createdAt,
        ?string $targetAudience = null,
        ?\DateTimeImmutable $scheduledFor = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->status = $status;
        $this->priority = $priority;
        $this->channels = $channels;
        $this->targetAudience = $targetAudience;
        $this->scheduledFor = $scheduledFor;
        $this->organizationId = $organizationId;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters
    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getType(): string { return $this->type; }
    public function getStatus(): string { return $this->status; }
    public function getPriority(): string { return $this->priority; }
    public function getChannels(): array { return $this->channels; }
    public function getTargetAudience(): ?string { return $this->targetAudience; }
    public function getScheduledFor(): ?\DateTimeImmutable { return $this->scheduledFor; }
    public function getOrganizationId(): string { return $this->organizationId; }
    public function getCreatedBy(): string { return $this->createdBy; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    // Setters
    public function setTitle(string $title): void { $this->title = $title; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setType(string $type): void { $this->type = $type; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setPriority(string $priority): void { $this->priority = $priority; }
    public function setChannels(array $channels): void { $this->channels = $channels; }
    public function setTargetAudience(?string $targetAudience): void { $this->targetAudience = $targetAudience; }
    public function setScheduledFor(?\DateTimeImmutable $scheduledFor): void { $this->scheduledFor = $scheduledFor; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}