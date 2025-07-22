<?php
namespace App\RequestInformation\Infrastructure\Repository;

use App\RequestInformation\Domain\Aggregate\RequestInformation;
use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use App\RequestInformation\Domain\ValueObject\RequestStatus;
use Doctrine\ORM\EntityManagerInterface;
use App\RequestInformation\Infrastructure\Persistence\DoctrineRequestInformationEntity;
use App\RequestInformation\Infrastructure\Persistence\RequestInformationMapper;
use Doctrine\ORM\EntityNotFoundException;

class DoctrineRequestInformationRepository implements RequestInformationRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(RequestInformation $request): void
    {
        $entity = RequestInformationMapper::toDoctrine($request);
        $this->em->persist($entity);
        $this->em->flush();
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
            ->where('r.status = :status')
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
            ->addSelect('r.status')
            ->from(DoctrineRequestInformationEntity::class, 'r');

        if ($from) {
            $qb->andWhere('r.createdAt >= :from')->setParameter('from', $from->format('Y-m-d 00:00:00'));
        }
        if ($to) {
            $qb->andWhere('r.createdAt <= :to')->setParameter('to', $to->format('Y-m-d 23:59:59'));
        }

        $qb->groupBy('r.status');

        $results = $qb->getQuery()->getArrayResult();

        $allStates = array_map(fn($e) => strtolower($e->value), RequestStatus::cases());

        // Procesa para retornar bien el resumen
        $summary = [
            'total' => 0,
            ...array_fill_keys($allStates, 0)
        ];
        foreach ($results as $row) {
            $summary[strtolower($row['status']->value)] = (int) $row['total'];
            $summary['total'] += (int)$row['total'];
        }
        return $summary;
    }

    public function findById(string $id): ?RequestInformation
    {

        /*$qb = $this->em->createQueryBuilder();
        $qb->select('r')
            ->from(DoctrineRequestInformationEntity::class, 'r')
            ->where('r.id = :id')
            ->setParameter('id', $id);
        return $qb->getQuery()->getOneOrNullResult();*/
    }
}
