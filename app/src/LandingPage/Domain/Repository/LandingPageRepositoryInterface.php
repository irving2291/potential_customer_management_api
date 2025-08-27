<?php

namespace App\LandingPage\Domain\Repository;

use App\LandingPage\Domain\Aggregate\LandingPage;

interface LandingPageRepositoryInterface
{
    public function findById(string $id): ?LandingPage;
    
    public function findBySlug(string $slug): ?LandingPage;
    
    public function findByOrganizationId(string $organizationId): array;
    
    public function findPublishedByOrganizationId(string $organizationId): array;
    
    public function save(LandingPage $landingPage): void;
    
    public function delete(string $id): void;
}