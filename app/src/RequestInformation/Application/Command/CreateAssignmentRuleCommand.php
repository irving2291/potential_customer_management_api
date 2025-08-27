<?php

namespace App\RequestInformation\Application\Command;

class CreateAssignmentRuleCommand
{
    public string $name;
    public ?string $description;
    public bool $active;
    public int $priority;
    public array $conditions;
    public string $assignmentType;
    public array $assigneeIds;
    public string $organizationId;

    public function __construct(
        string $name,
        ?string $description,
        bool $active,
        int $priority,
        array $conditions,
        string $assignmentType,
        array $assigneeIds,
        string $organizationId
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->active = $active;
        $this->priority = $priority;
        $this->conditions = $conditions;
        $this->assignmentType = $assignmentType;
        $this->assigneeIds = $assigneeIds;
        $this->organizationId = $organizationId;
    }
}