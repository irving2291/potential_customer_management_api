<?php

namespace App\Quotation\Infrastructure\Repository;

use App\Quotation\Domain\Aggregate\Quotation;
use App\Quotation\Domain\Repository\QuotationRepositoryInterface;
use App\Quotation\Infrastructure\Persistence\DoctrineQuotationEntity;
use App\Quotation\Infrastructure\Persistence\QuotationMapper;
use App\RequestInformation\Infrastructure\Persistence\DoctrineRequestInformationEntity;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineQuotationRepository implements QuotationRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(Quotation $quotation): void
    {
        $requestInfoEntity = $this->em
            ->getRepository(DoctrineRequestInformationEntity::class)
            ->find($quotation->getRequestInformationId());

        if (!$requestInfoEntity) {
            throw new \DomainException('RequestInformation not found.');
        }

        $repo = $this->em->getRepository(DoctrineQuotationEntity::class);
        $doctrineEntity = $repo->find($quotation->getId());

        // Usa el Mapper (crea nuevo o actualiza)
        $entity = QuotationMapper::toDoctrine($quotation, $requestInfoEntity, $doctrineEntity);

        $this->em->persist($entity);
        $this->em->flush();
    }

    public function findById(string $id): ?Quotation
    {
        $entity = $this->em
            ->getRepository(DoctrineQuotationEntity::class)
            ->find($id);

        return $entity ? QuotationMapper::toDomain($entity) : null;
    }

    public function findByRequestInformationId(string $requestInformationId): ?Quotation
    {
        $requestInfoEntity = $this->em
            ->getRepository(DoctrineRequestInformationEntity::class)
            ->find($requestInformationId);

        if (!$requestInfoEntity) {
            return null;
        }

        $entity = $this->em
            ->getRepository(DoctrineQuotationEntity::class)
            ->findOneBy(['requestInformation' => $requestInfoEntity]);

        return $entity ? QuotationMapper::toDomain($entity) : null;
    }

    public function existsActiveQuotationForRequest(string $requestInformationId, array $statuses = []): bool
    {
        $requestInfoEntity = $this->em
            ->getRepository(DoctrineRequestInformationEntity::class)
            ->find($requestInformationId);

        if (!$requestInfoEntity) {
            return false;
        }

        $statuses = $statuses ?: ['creating', 'sent', 'accepted'];

        $qb = $this->em->createQueryBuilder();
        $qb->select('count(q.id)')
            ->from(DoctrineQuotationEntity::class, 'q')
            ->where('q.requestInformation = :requestInformation')
            ->andWhere($qb->expr()->in('q.status', ':statuses'))
            ->setParameter('requestInformation', $requestInfoEntity)
            ->setParameter('statuses', $statuses);

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function paginateByOrgId(
        string $orgId,
        int $page = 1,
        int $perPage = 20,
        ?string $orderBy = 'createdAt',
        string $direction = 'DESC'
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        $qb = $this->em->createQueryBuilder();
        $qb->select('q')
            ->from(DoctrineQuotationEntity::class, 'q')
            ->andWhere('q.organizationId = :orgId')
            ->andWhere('q.deletedAt IS NULL') // opcional: filtra soft-deleted
            ->setParameter('orgId', $orgId);

        if ($orderBy !== null) {
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
            $qb->orderBy('q.' . $orderBy, $direction);
        }

        $qb->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($qb, true);
        $total = count($paginator);

        $items = [];
        foreach ($paginator as $quotation) {
            $items[] = $quotation; // devuelve entidades Quotation
        }

        return [
            'items'   => $items,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'pages'   => (int) ceil($total / $perPage),
        ];
    }
}
