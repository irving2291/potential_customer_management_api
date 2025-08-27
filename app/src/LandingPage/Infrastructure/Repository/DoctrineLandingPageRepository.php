<?php

namespace App\LandingPage\Infrastructure\Repository;

use App\LandingPage\Domain\Aggregate\LandingPage;
use App\LandingPage\Domain\Repository\LandingPageRepositoryInterface;
use App\LandingPage\Infrastructure\Persistence\DoctrineLandingPageEntity;
use App\LandingPage\Infrastructure\Persistence\LandingPageMapper;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineLandingPageRepository implements LandingPageRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findById(string $id): ?LandingPage
    {
        $entity = $this->entityManager->find(DoctrineLandingPageEntity::class, $id);
        
        if (!$entity) {
            return null;
        }

        return LandingPageMapper::toDomain($entity);
    }

    public function findBySlug(string $slug): ?LandingPage
    {
        $entity = $this->entityManager->getRepository(DoctrineLandingPageEntity::class)
            ->findOneBy(['slug' => $slug, 'isPublished' => true]);

        if (!$entity) {
            return null;
        }

        return LandingPageMapper::toDomain($entity);
    }

    public function findByOrganizationId(string $organizationId): array
    {
        $entities = $this->entityManager->getRepository(DoctrineLandingPageEntity::class)
            ->findBy(['organizationId' => $organizationId], ['createdAt' => 'DESC']);

        return array_map(
            fn(DoctrineLandingPageEntity $entity) => LandingPageMapper::toDomain($entity),
            $entities
        );
    }

    public function findPublishedByOrganizationId(string $organizationId): array
    {
        $entities = $this->entityManager->getRepository(DoctrineLandingPageEntity::class)
            ->findBy([
                'organizationId' => $organizationId,
                'isPublished' => true
            ], ['createdAt' => 'DESC']);

        return array_map(
            fn(DoctrineLandingPageEntity $entity) => LandingPageMapper::toDomain($entity),
            $entities
        );
    }

    public function save(LandingPage $landingPage): void
    {
        $existingEntity = $this->entityManager->find(DoctrineLandingPageEntity::class, $landingPage->getId());

        if ($existingEntity) {
            LandingPageMapper::updateEntity($existingEntity, $landingPage);
        } else {
            $entity = LandingPageMapper::toEntity($landingPage);
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    public function delete(string $id): void
    {
        $entity = $this->entityManager->find(DoctrineLandingPageEntity::class, $id);
        
        if ($entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }
    }
}