<?php
namespace App\RequestInformation\Infrastructure\Repository;

use App\RequestInformation\Domain\Aggregate\RequestInformation;
use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use App\RequestInformation\Domain\ValueObject\RequestStatus;
use App\RequestInformation\Infrastructure\Persistence\DoctrineRequestInformationStatusEntity;
use Doctrine\ORM\EntityManagerInterface;
use App\RequestInformation\Infrastructure\Persistence\DoctrineRequestInformationEntity;
use App\RequestInformation\Infrastructure\Persistence\RequestInformationMapper;
use Doctrine\ORM\EntityNotFoundException;

class DoctrineRequestInformationRepository implements RequestInformationRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(RequestInformation $request): RequestInformation
    {
        $statusCode = $request->getStatus()->getCode();

        $statusEntity = $this->em->getRepository(DoctrineRequestInformationStatusEntity::class)
            ->findOneBy(['code' => $statusCode, 'organizationId' => $request->getOrganizationId()]);

        if (!$statusEntity) {
            throw new \RuntimeException("Status entity not found");
        }
        $entity = null;
        if ($request->getId()) {
            $entity = $this->em->getRepository(DoctrineRequestInformationEntity::class)
                ->find($request->getId());
        }


        $entity = RequestInformationMapper::toDoctrine($request, $statusEntity, $entity);
        $this->em->persist($entity);
        $this->em->flush();
        return RequestInformationMapper::toDomain($entity);
    }

    /**
     * @param string $email
     * @param string $programId
     * @param string $leadId
     * @return bool
     */
    public function existsByEmailProgramAndLeadInThreeMonth(string $email, string $programId, string $leadId): bool
    {
        $threeMonthsAgo = (new \DateTime())->modify('-3 months');

        $qb = $this->em->createQueryBuilder();
        $qb->select('count(r.id)')
            ->from(DoctrineRequestInformationEntity::class, 'r')
            ->where('r.email = :email')
            ->andWhere('r.programInterestId = :program')
            ->andWhere('r.leadOriginId = :lead')
            ->andWhere('r.createdAt >= :date')
            ->setParameter('email', $email)
            ->setParameter('program', $programId)
            ->setParameter('lead', $leadId)
            ->setParameter('date', $threeMonthsAgo);

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function findByStatusPaginated(
        string $status,
        int $page = 1,
        int $limit = 10
    ): array {
        $qb = $this->em->createQueryBuilder();
        $qb->select('r')
            ->from(DoctrineRequestInformationEntity::class, 'r')
            ->innerJoin('r.status', 's')
            ->where('s.code = :status')
            ->setParameter('status', $status)
            ->orderBy('r.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function getSummaryByDates(\DateTimeInterface $from = null, \DateTimeInterface $to = null): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('COUNT(r.id) as total')
            ->addSelect('LOWER(s.code) as code')
            ->from(DoctrineRequestInformationEntity::class, 'r')
            ->join('r.status', 's');

        if ($from) {
            $qb->andWhere('r.createdAt >= :from')->setParameter('from', $from->format('Y-m-d 00:00:00'));
        }
        if ($to) {
            $qb->andWhere('r.createdAt <= :to')->setParameter('to', $to->format('Y-m-d 23:59:59'));
        }

        $qb->groupBy('s.code');

        $results = $qb->getQuery()->getArrayResult();

        // Obtener todos los codes posibles de status (desde la base)
        $allStates = $this->em->getRepository(DoctrineRequestInformationStatusEntity::class)
            ->createQueryBuilder('s')
            ->select('LOWER(s.code) as code')
            ->getQuery()
            ->getArrayResult();
        $allStates = array_column($allStates, 'code');

        $summary = [
            'total' => 0,
            ...array_fill_keys($allStates, 0)
        ];

        foreach ($results as $row) {
            $summary[$row['code']] = (int) $row['total'];
            $summary['total'] += (int)$row['total'];
        }
        return $summary;
    }

    public function findById(string $id): ?RequestInformation
    {
        $repo = $this->em->getRepository(DoctrineRequestInformationEntity::class);
        $entity = $repo->find($id);
        if (!$entity) {
            throw new EntityNotFoundException();
        }
        return RequestInformationMapper::toDomain($entity);
    }
}
