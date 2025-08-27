<?php

namespace App\Assignee\Infrastructure\Repository;

use App\Assignee\Domain\Aggregate\Assignee;
use App\Assignee\Domain\Repository\AssigneeRepositoryInterface;
use App\Assignee\Infrastructure\Persistence\DoctrineAssigneeEntity;
use App\Assignee\Infrastructure\Persistence\AssigneeMapper;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineAssigneeRepository implements AssigneeRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findById(string $id): ?Assignee
    {
        $entity = $this->entityManager->find(DoctrineAssigneeEntity::class, $id);
        
        if (!$entity) {
            return null;
        }

        return AssigneeMapper::toDomain($entity);
    }

    public function findByOrganizationId(string $organizationId): array
    {
        $entities = $this->entityManager->getRepository(DoctrineAssigneeEntity::class)
            ->findBy(['organizationId' => $organizationId], ['firstName' => 'ASC']);

        return array_map(
            fn(DoctrineAssigneeEntity $entity) => AssigneeMapper::toDomain($entity),
            $entities
        );
    }

    public function findActiveByOrganizationId(string $organizationId): array
    {
        $entities = $this->entityManager->getRepository(DoctrineAssigneeEntity::class)
            ->findBy([
                'organizationId' => $organizationId,
                'active' => true
            ], ['firstName' => 'ASC']);

        return array_map(
            fn(DoctrineAssigneeEntity $entity) => AssigneeMapper::toDomain($entity),
            $entities
        );
    }

    public function save(Assignee $assignee): void
    {
        $existingEntity = $this->entityManager->find(DoctrineAssigneeEntity::class, $assignee->getId());

        if ($existingEntity) {
            AssigneeMapper::updateEntity($existingEntity, $assignee);
        } else {
            $entity = AssigneeMapper::toEntity($assignee);
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    public function delete(string $id): void
    {
        $entity = $this->entityManager->find(DoctrineAssigneeEntity::class, $id);
        
        if ($entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }
    }

    public function findByEmail(string $email): ?Assignee
    {
        $entity = $this->entityManager->getRepository(DoctrineAssigneeEntity::class)
            ->findOneBy(['email' => $email]);

        if (!$entity) {
            return null;
        }

        return AssigneeMapper::toDomain($entity);
    }
}