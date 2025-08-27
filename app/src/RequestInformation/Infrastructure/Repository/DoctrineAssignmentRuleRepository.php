<?php

namespace App\RequestInformation\Infrastructure\Repository;

use App\RequestInformation\Domain\Aggregate\AssignmentRule;
use App\RequestInformation\Domain\Repository\AssignmentRuleRepositoryInterface;
use App\RequestInformation\Infrastructure\Persistence\DoctrineAssignmentRuleEntity;
use App\RequestInformation\Infrastructure\Persistence\AssignmentRuleMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class DoctrineAssignmentRuleRepository implements AssignmentRuleRepositoryInterface
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(DoctrineAssignmentRuleEntity::class);
    }

    public function save(AssignmentRule $assignmentRule): void
    {
        $existingEntity = $this->repository->find($assignmentRule->getId());
        
        if ($existingEntity) {
            // Update existing entity
            AssignmentRuleMapper::updateEntity($existingEntity, $assignmentRule);
        } else {
            // Create new entity
            $entity = AssignmentRuleMapper::toEntity($assignmentRule);
            $this->entityManager->persist($entity);
        }
        
        $this->entityManager->flush();
    }

    public function findById(string $id): ?AssignmentRule
    {
        $entity = $this->repository->find($id);
        
        if (!$entity) {
            return null;
        }
        
        return AssignmentRuleMapper::toDomain($entity);
    }

    public function findByOrganizationId(string $organizationId): array
    {
        $entities = $this->repository->findBy(
            ['organizationId' => $organizationId],
            ['priority' => 'ASC', 'name' => 'ASC']
        );
        
        return array_map([AssignmentRuleMapper::class, 'toDomain'], $entities);
    }

    public function findActiveByOrganizationId(string $organizationId): array
    {
        $entities = $this->repository->findBy(
            [
                'organizationId' => $organizationId,
                'active' => true
            ],
            ['priority' => 'ASC', 'name' => 'ASC']
        );
        
        return array_map([AssignmentRuleMapper::class, 'toDomain'], $entities);
    }

    public function findActiveByOrganizationIdOrderedByPriority(string $organizationId): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        
        $queryBuilder
            ->select('ar')
            ->from(DoctrineAssignmentRuleEntity::class, 'ar')
            ->where('ar.organizationId = :organizationId')
            ->andWhere('ar.active = :active')
            ->orderBy('ar.priority', 'ASC')
            ->addOrderBy('ar.name', 'ASC')
            ->setParameter('organizationId', $organizationId)
            ->setParameter('active', true);
        
        $entities = $queryBuilder->getQuery()->getResult();
        
        return array_map([AssignmentRuleMapper::class, 'toDomain'], $entities);
    }

    public function delete(AssignmentRule $assignmentRule): void
    {
        $entity = $this->repository->find($assignmentRule->getId());
        
        if ($entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }
    }

    public function existsByNameAndOrganization(string $name, string $organizationId, ?string $excludeId = null): bool
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        
        $queryBuilder
            ->select('COUNT(ar.id)')
            ->from(DoctrineAssignmentRuleEntity::class, 'ar')
            ->where('ar.name = :name')
            ->andWhere('ar.organizationId = :organizationId')
            ->setParameter('name', $name)
            ->setParameter('organizationId', $organizationId);
        
        if ($excludeId) {
            $queryBuilder
                ->andWhere('ar.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }
        
        $count = $queryBuilder->getQuery()->getSingleScalarResult();
        
        return $count > 0;
    }
}