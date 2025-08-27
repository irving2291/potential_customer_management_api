<?php

namespace App\PotentialCustomer\Domain\Repository;

use App\PotentialCustomer\Domain\Aggregate\PotentialCustomer;

interface PotentialCustomerRepositoryInterface
{
    public function save(PotentialCustomer $customer): void;

    public function findOneByEmail(string $email): ?PotentialCustomer;
    
    public function findById(string $id): ?PotentialCustomer;
    
    public function findByOrganizationId(string $organizationId, array $filters = [], int $page = 1, int $perPage = 20): array;
    
    public function countByOrganizationId(string $organizationId, array $filters = []): int;
    
    public function getStatsByOrganizationId(string $organizationId): array;
    
    public function delete(string $id): void;
}
