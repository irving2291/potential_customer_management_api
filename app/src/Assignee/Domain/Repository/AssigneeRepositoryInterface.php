<?php

namespace App\Assignee\Domain\Repository;

use App\Assignee\Domain\Aggregate\Assignee;

interface AssigneeRepositoryInterface
{
    public function findById(string $id): ?Assignee;
    
    public function findByOrganizationId(string $organizationId): array;
    
    public function findActiveByOrganizationId(string $organizationId): array;
    
    public function save(Assignee $assignee): void;
    
    public function delete(string $id): void;
    
    public function findByEmail(string $email): ?Assignee;

    public function findByUserId(string $userId): array;
}