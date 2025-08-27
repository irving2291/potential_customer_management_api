<?php

namespace App\Activation\Infrastructure\Repository;

use App\Activation\Domain\Aggregate\Activation;
use App\Activation\Domain\Repository\ActivationRepositoryInterface;
use App\Activation\Infrastructure\Persistence\DoctrineActivationEntity;
use App\Activation\Infrastructure\Persistence\ActivationMapper;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineActivationRepository implements ActivationRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findById(string $id): ?Activation
    {
        $entity = $this->entityManager->find(DoctrineActivationEntity::class, $id);
        
        if (!$entity) {
            return null;
        }

        return ActivationMapper::toDomain($entity);
    }

    public function findByOrganizationId(string $organizationId): array
    {
        $entities = $this->entityManager->getRepository(DoctrineActivationEntity::class)
            ->findBy(['organizationId' => $organizationId], ['createdAt' => 'DESC']);

        return array_map(
            fn(DoctrineActivationEntity $entity) => ActivationMapper::toDomain($entity),
            $entities
        );
    }

    public function findByOrganizationIdPaginated(
        string $organizationId,
        int $page = 1,
        int $perPage = 20,
        ?string $status = null,
        ?string $type = null,
        ?string $search = null
    ): array {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')
           ->from(DoctrineActivationEntity::class, 'a')
           ->where('a.organizationId = :organizationId')
           ->setParameter('organizationId', $organizationId);

        if ($status) {
            $qb->andWhere('a.status = :status')
               ->setParameter('status', $status);
        }

        if ($type) {
            $qb->andWhere('a.type = :type')
               ->setParameter('type', $type);
        }

        if ($search) {
            $qb->andWhere('(a.title LIKE :search OR a.description LIKE :search)')
               ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('a.createdAt', 'DESC');

        // Calculate offset
        $offset = ($page - 1) * $perPage;
        $qb->setFirstResult($offset)
           ->setMaxResults($perPage);

        $entities = $qb->getQuery()->getResult();

        return array_map(
            fn(DoctrineActivationEntity $entity) => ActivationMapper::toDomain($entity),
            $entities
        );
    }

    public function save(Activation $activation): void
    {
        $existingEntity = $this->entityManager->find(DoctrineActivationEntity::class, $activation->getId());

        if ($existingEntity) {
            ActivationMapper::updateEntity($existingEntity, $activation);
        } else {
            $entity = ActivationMapper::toEntity($activation);
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    public function delete(string $id): void
    {
        $entity = $this->entityManager->find(DoctrineActivationEntity::class, $id);
        
        if ($entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }
    }

    public function findScheduledActivations(): array
    {
        $entities = $this->entityManager->getRepository(DoctrineActivationEntity::class)
            ->findBy(['status' => 'scheduled'], ['scheduledFor' => 'ASC']);

        return array_map(
            fn(DoctrineActivationEntity $entity) => ActivationMapper::toDomain($entity),
            $entities
        );
    }

    public function findActiveActivations(): array
    {
        $entities = $this->entityManager->getRepository(DoctrineActivationEntity::class)
            ->findBy(['status' => 'active'], ['createdAt' => 'DESC']);

        return array_map(
            fn(DoctrineActivationEntity $entity) => ActivationMapper::toDomain($entity),
            $entities
        );
    }
}