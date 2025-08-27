<?php

namespace App\Activation\Domain\Repository;

use App\Activation\Domain\Aggregate\Activation;

interface ActivationRepositoryInterface
{
    public function findById(string $id): ?Activation;
    
    public function findByOrganizationId(string $organizationId): array;
    
    public function findByOrganizationIdPaginated(
        string $organizationId,
        int $page = 1,
        int $perPage = 20,
        ?string $status = null,
        ?string $type = null,
        ?string $search = null
    ): array;
    
    public function save(Activation $activation): void;
    
    public function delete(string $id): void;
    
    public function findScheduledActivations(): array;
    
    public function findActiveActivations(): array;
}