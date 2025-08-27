<?php

namespace App\PotentialCustomer\Infrastructure\Repository;

use App\PotentialCustomer\Domain\Repository\PotentialCustomerRepositoryInterface;
use App\PotentialCustomer\Domain\Aggregate\PotentialCustomer;
use App\PotentialCustomer\Infrastructure\Persistence\DoctrinePotentialCustomerEntity;
use App\PotentialCustomer\Infrastructure\Persistence\PotentialCustomerMapper;
use Doctrine\ORM\EntityManagerInterface;

class DoctrinePotentialCustomerRepository implements PotentialCustomerRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findOneByEmail(string $email): ?PotentialCustomer
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('pc')
            ->from(DoctrinePotentialCustomerEntity::class, 'pc')
            ->leftJoin('pc.emails', 'e')
            ->where('LOWER(e.value) = :email')
            ->setParameter('email', strtolower($email))
            ->setMaxResults(1);

        $entity = $qb->getQuery()->getOneOrNullResult();

        if (!$entity) {
            return null;
        }
        return PotentialCustomerMapper::toDomain($entity);
    }

    public function findById(string $id): ?PotentialCustomer
    {
        $entity = $this->em->find(DoctrinePotentialCustomerEntity::class, $id);
        
        if (!$entity) {
            return null;
        }

        return PotentialCustomerMapper::toDomain($entity);
    }

    public function findByOrganizationId(string $organizationId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('pc')
            ->from(DoctrinePotentialCustomerEntity::class, 'pc')
            ->where('pc.organizationId = :organizationId')
            ->setParameter('organizationId', $organizationId)
            ->orderBy('pc.createdAt', 'DESC');

        // Apply filters
        if (isset($filters['type']) && $filters['type'] !== 'all') {
            $qb->andWhere('pc.type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $qb->andWhere('pc.status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $qb->andWhere('pc.priority = :priority')
               ->setParameter('priority', $filters['priority']);
        }

        if (isset($filters['assignedTo'])) {
            $qb->andWhere('pc.assignedTo = :assignedTo')
               ->setParameter('assignedTo', $filters['assignedTo']);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $qb->leftJoin('pc.emails', 'e')
               ->andWhere($qb->expr()->orX(
                   'LOWER(pc.firstName) LIKE :search',
                   'LOWER(pc.lastName) LIKE :search',
                   'LOWER(pc.companyName) LIKE :search',
                   'LOWER(e.value) LIKE :search'
               ))
               ->setParameter('search', '%' . $search . '%');
        }

        // Pagination
        $offset = ($page - 1) * $perPage;
        $qb->setFirstResult($offset)
           ->setMaxResults($perPage);

        $entities = $qb->getQuery()->getResult();

        return array_map(
            fn(DoctrinePotentialCustomerEntity $entity) => PotentialCustomerMapper::toDomain($entity),
            $entities
        );
    }

    public function countByOrganizationId(string $organizationId, array $filters = []): int
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('COUNT(pc.id)')
            ->from(DoctrinePotentialCustomerEntity::class, 'pc')
            ->where('pc.organizationId = :organizationId')
            ->setParameter('organizationId', $organizationId);

        // Apply same filters as in findByOrganizationId
        if (isset($filters['type']) && $filters['type'] !== 'all') {
            $qb->andWhere('pc.type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $qb->andWhere('pc.status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $qb->andWhere('pc.priority = :priority')
               ->setParameter('priority', $filters['priority']);
        }

        if (isset($filters['assignedTo'])) {
            $qb->andWhere('pc.assignedTo = :assignedTo')
               ->setParameter('assignedTo', $filters['assignedTo']);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $qb->leftJoin('pc.emails', 'e')
               ->andWhere($qb->expr()->orX(
                   'LOWER(pc.firstName) LIKE :search',
                   'LOWER(pc.lastName) LIKE :search',
                   'LOWER(pc.companyName) LIKE :search',
                   'LOWER(e.value) LIKE :search'
               ))
               ->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getStatsByOrganizationId(string $organizationId): array
    {
        $qb = $this->em->createQueryBuilder();
        
        // Total accounts
        $totalAccounts = $qb->select('COUNT(pc.id)')
            ->from(DoctrinePotentialCustomerEntity::class, 'pc')
            ->where('pc.organizationId = :organizationId')
            ->setParameter('organizationId', $organizationId)
            ->getQuery()
            ->getSingleScalarResult();

        // Total clients
        $totalClients = $qb->select('COUNT(pc.id)')
            ->from(DoctrinePotentialCustomerEntity::class, 'pc')
            ->where('pc.organizationId = :organizationId')
            ->andWhere('pc.status = :status')
            ->setParameter('organizationId', $organizationId)
            ->setParameter('status', 'client')
            ->getQuery()
            ->getSingleScalarResult();

        // Total prospects
        $totalProspects = $qb->select('COUNT(pc.id)')
            ->from(DoctrinePotentialCustomerEntity::class, 'pc')
            ->where('pc.organizationId = :organizationId')
            ->andWhere('pc.status = :status')
            ->setParameter('organizationId', $organizationId)
            ->setParameter('status', 'prospect')
            ->getQuery()
            ->getSingleScalarResult();

        // Total values
        $totalValue = $qb->select('SUM(pc.totalValue)')
            ->from(DoctrinePotentialCustomerEntity::class, 'pc')
            ->where('pc.organizationId = :organizationId')
            ->setParameter('organizationId', $organizationId)
            ->getQuery()
            ->getSingleScalarResult() ?: 0;

        $potentialValue = $qb->select('SUM(pc.potentialValue)')
            ->from(DoctrinePotentialCustomerEntity::class, 'pc')
            ->where('pc.organizationId = :organizationId')
            ->setParameter('organizationId', $organizationId)
            ->getQuery()
            ->getSingleScalarResult() ?: 0;

        // By type
        $byType = $this->em->createQuery(
            'SELECT pc.type, COUNT(pc.id) as count
             FROM ' . DoctrinePotentialCustomerEntity::class . ' pc
             WHERE pc.organizationId = :organizationId
             GROUP BY pc.type'
        )->setParameter('organizationId', $organizationId)->getResult();

        $typeStats = ['person' => 0, 'company' => 0];
        foreach ($byType as $stat) {
            $typeStats[$stat['type']] = (int) $stat['count'];
        }

        // By priority
        $byPriority = $this->em->createQuery(
            'SELECT pc.priority, COUNT(pc.id) as count
             FROM ' . DoctrinePotentialCustomerEntity::class . ' pc
             WHERE pc.organizationId = :organizationId
             GROUP BY pc.priority'
        )->setParameter('organizationId', $organizationId)->getResult();

        $priorityStats = ['low' => 0, 'medium' => 0, 'high' => 0];
        foreach ($byPriority as $stat) {
            $priorityStats[$stat['priority']] = (int) $stat['count'];
        }

        return [
            'totalAccounts' => (int) $totalAccounts,
            'totalClients' => (int) $totalClients,
            'totalProspects' => (int) $totalProspects,
            'conversionRate' => $totalAccounts > 0 ? round($totalClients / $totalAccounts, 2) : 0,
            'totalValue' => (float) $totalValue,
            'potentialValue' => (float) $potentialValue,
            'byType' => $typeStats,
            'byPriority' => $priorityStats
        ];
    }

    public function save(PotentialCustomer $customer): void
    {
        $existingEntity = $this->em->find(DoctrinePotentialCustomerEntity::class, $customer->getId());

        if ($existingEntity) {
            PotentialCustomerMapper::updateEntity($existingEntity, $customer);
        } else {
            $entity = PotentialCustomerMapper::toDoctrine($customer);
            $this->em->persist($entity);
        }

        $this->em->flush();
    }

    public function delete(string $id): void
    {
        $entity = $this->em->find(DoctrinePotentialCustomerEntity::class, $id);
        
        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();
        }
    }
}
