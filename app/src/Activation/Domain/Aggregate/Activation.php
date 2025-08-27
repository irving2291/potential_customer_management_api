<?php

namespace App\Activation\Domain\Aggregate;

use App\Common\Domain\DomainEventRecorder;

class Activation
{
    use DomainEventRecorder;

    private string $id;
    private string $title;
    private string $description;
    private string $type; // promotion, announcement, reminder, survey
    private string $status; // draft, scheduled, active, completed, cancelled
    private string $priority; // low, medium, high, urgent
    private array $channels; // email, sms, whatsapp
    private ?string $targetAudience;
    private ?\DateTimeImmutable $scheduledFor;
    private string $organizationId;
    private string $createdBy;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $title,
        string $description,
        string $type,
        string $priority,
        array $channels,
        string $organizationId,
        string $createdBy,
        ?string $targetAudience = null,
        ?\DateTimeImmutable $scheduledFor = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->status = 'draft';
        $this->priority = $priority;
        $this->channels = $channels;
        $this->targetAudience = $targetAudience;
        $this->scheduledFor = $scheduledFor;
        $this->organizationId = $organizationId;
        $this->createdBy = $createdBy;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function getChannels(): array
    {
        return $this->channels;
    }

    public function getTargetAudience(): ?string
    {
        return $this->targetAudience;
    }

    public function getScheduledFor(): ?\DateTimeImmutable
    {
        return $this->scheduledFor;
    }

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    public function getCreatedBy(): string
    {
        return $this->createdBy;
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
        string $title,
        string $description,
        string $type,
        string $priority,
        array $channels,
        ?string $targetAudience = null,
        ?\DateTimeImmutable $scheduledFor = null
    ): void {
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->priority = $priority;
        $this->channels = $channels;
        $this->targetAudience = $targetAudience;
        $this->scheduledFor = $scheduledFor;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function activate(): void
    {
        if ($this->status !== 'draft' && $this->status !== 'scheduled') {
            throw new \DomainException('Cannot activate activation in current status: ' . $this->status);
        }

        $this->status = 'active';
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function pause(): void
    {
        if ($this->status !== 'active') {
            throw new \DomainException('Cannot pause activation in current status: ' . $this->status);
        }

        $this->status = 'scheduled'; // Paused activations go back to scheduled
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function complete(): void
    {
        if ($this->status !== 'active') {
            throw new \DomainException('Cannot complete activation in current status: ' . $this->status);
        }

        $this->status = 'completed';
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function cancel(): void
    {
        if ($this->status === 'completed' || $this->status === 'cancelled') {
            throw new \DomainException('Cannot cancel activation in current status: ' . $this->status);
        }

        $this->status = 'cancelled';
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function schedule(\DateTimeImmutable $scheduledFor): void
    {
        if ($this->status !== 'draft') {
            throw new \DomainException('Cannot schedule activation in current status: ' . $this->status);
        }

        $this->scheduledFor = $scheduledFor;
        $this->status = 'scheduled';
        $this->updatedAt = new \DateTimeImmutable();
    }
}