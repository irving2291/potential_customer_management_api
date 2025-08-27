<?php

namespace App\RequestInformation\Domain\Aggregate;

use App\Common\Domain\DomainEventRecorder;

class AssignmentRule
{
    use DomainEventRecorder;

    private string $id;
    private string $name;
    private ?string $description;
    private bool $active;
    private int $priority;
    private array $conditions;
    private string $assignmentType;
    private array $assigneeIds;
    private string $organizationId;
    private \DateTimeImmutable $createdAt;
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
        string $organizationId
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
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = null;
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

    public function activate(): void
    {
        $this->active = true;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->active = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function matchesConditions(array $requestData): bool
    {
        if (empty($this->conditions)) {
            return true; // No conditions means it matches all
        }

        foreach ($this->conditions as $condition) {
            if (!$this->evaluateCondition($condition, $requestData)) {
                return false;
            }
        }

        return true;
    }

    private function evaluateCondition(array $condition, array $requestData): bool
    {
        $field = $condition['field'] ?? '';
        $operator = $condition['operator'] ?? '';
        $value = $condition['value'] ?? '';

        if (!isset($requestData[$field])) {
            return false;
        }

        $fieldValue = $requestData[$field];

        return match ($operator) {
            'equals' => $fieldValue == $value,
            'not_equals' => $fieldValue != $value,
            'contains' => str_contains(strtolower($fieldValue), strtolower($value)),
            'not_contains' => !str_contains(strtolower($fieldValue), strtolower($value)),
            'greater_than' => is_numeric($fieldValue) && is_numeric($value) && $fieldValue > $value,
            'less_than' => is_numeric($fieldValue) && is_numeric($value) && $fieldValue < $value,
            'greater_equal' => is_numeric($fieldValue) && is_numeric($value) && $fieldValue >= $value,
            'less_equal' => is_numeric($fieldValue) && is_numeric($value) && $fieldValue <= $value,
            'in' => is_array($value) && in_array($fieldValue, $value),
            'not_in' => is_array($value) && !in_array($fieldValue, $value),
            default => false,
        };
    }

    public function getNextAssignee(array $assignmentContext = []): ?string
    {
        if (empty($this->assigneeIds)) {
            return null;
        }

        return match ($this->assignmentType) {
            'round_robin' => $this->getRoundRobinAssignee($assignmentContext),
            'load_balanced' => $this->getLoadBalancedAssignee($assignmentContext),
            'skill_based' => $this->getSkillBasedAssignee($assignmentContext),
            'manual' => $this->assigneeIds[0] ?? null,
            'random' => $this->assigneeIds[array_rand($this->assigneeIds)],
            default => $this->assigneeIds[0] ?? null,
        };
    }

    private function getRoundRobinAssignee(array $context): ?string
    {
        $lastAssigneeIndex = $context['lastAssigneeIndex'] ?? -1;
        $nextIndex = ($lastAssigneeIndex + 1) % count($this->assigneeIds);
        return $this->assigneeIds[$nextIndex] ?? null;
    }

    private function getLoadBalancedAssignee(array $context): ?string
    {
        $assigneeLoads = $context['assigneeLoads'] ?? [];
        
        // Find assignee with minimum load
        $minLoad = PHP_INT_MAX;
        $selectedAssignee = null;
        
        foreach ($this->assigneeIds as $assigneeId) {
            $load = $assigneeLoads[$assigneeId] ?? 0;
            if ($load < $minLoad) {
                $minLoad = $load;
                $selectedAssignee = $assigneeId;
            }
        }
        
        return $selectedAssignee;
    }

    private function getSkillBasedAssignee(array $context): ?string
    {
        $requiredSkills = $context['requiredSkills'] ?? [];
        $assigneeSkills = $context['assigneeSkills'] ?? [];
        
        // Find assignee with best skill match
        $bestMatch = null;
        $bestScore = -1;
        
        foreach ($this->assigneeIds as $assigneeId) {
            $skills = $assigneeSkills[$assigneeId] ?? [];
            $score = count(array_intersect($requiredSkills, $skills));
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $assigneeId;
            }
        }
        
        return $bestMatch ?? $this->assigneeIds[0] ?? null;
    }
}