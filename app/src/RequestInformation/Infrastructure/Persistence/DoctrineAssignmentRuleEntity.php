<?php

namespace App\RequestInformation\Infrastructure\Persistence;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'assignment_rules')]
class DoctrineAssignmentRuleEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    #[ORM\Column(type: 'integer')]
    private int $priority;

    #[ORM\Column(type: 'json')]
    private array $conditions;

    #[ORM\Column(type: 'string', length: 50)]
    private string $assignmentType;

    #[ORM\Column(type: 'json')]
    private array $assigneeIds;

    #[ORM\Column(type: 'string', length: 36)]
    private string $organizationId;

    #[ORM\Column(type: "datetime_immutable", options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $name,
        ?string $description,
        bool $active,
        int $priority,
        array $conditions,
        string $assignmentType,
        array $assigneeIds,
        string $organizationId,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->active = $active;
        $this->priority = $priority;
        $this->conditions = $conditions;
        $this->assignmentType = $assignmentType;
        $this->assigneeIds = $assigneeIds;
        $this->organizationId = $organizationId;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function getAssignmentType(): string
    {
        return $this->assignmentType;
    }

    public function getAssigneeIds(): array
    {
        return $this->assigneeIds;
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

    public function updateRule(
        string $name,
        ?string $description,
        bool $active,
        int $priority,
        array $conditions,
        string $assignmentType,
        array $assigneeIds
    ): void {
        $this->name = $name;
        $this->description = $description;
        $this->active = $active;
        $this->priority = $priority;
        $this->conditions = $conditions;
        $this->assignmentType = $assignmentType;
        $this->assigneeIds = $assigneeIds;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
