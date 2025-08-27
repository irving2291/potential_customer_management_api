<?php

namespace App\RequestInformation\Domain\Repository;

use App\RequestInformation\Domain\Aggregate\AssignmentRule;

interface AssignmentRuleRepositoryInterface
{
    public function save(AssignmentRule $assignmentRule): void;
    
    public function findById(string $id): ?AssignmentRule;
    
    public function findByOrganizationId(string $organizationId): array;
    
    public function findActiveByOrganizationId(string $organizationId): array;
    
    public function findActiveByOrganizationIdOrderedByPriority(string $organizationId): array;
    
    public function delete(AssignmentRule $assignmentRule): void;
    
    public function existsByNameAndOrganization(string $name, string $organizationId, ?string $excludeId = null): bool;
}