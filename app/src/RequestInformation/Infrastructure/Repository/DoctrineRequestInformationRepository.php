<?php
namespace App\RequestInformation\Infrastructure\Repository;

use App\RequestInformation\Domain\Aggregate\RequestInformation;
use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\RequestInformation\Infrastructure\Persistence\DoctrineRequestInformationEntity;
use App\RequestInformation\Infrastructure\Persistence\RequestInformationMapper;

readonly class DoctrineRequestInformationRepository implements RequestInformationRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(RequestInformation $request): void
    {
        $entity = RequestInformationMapper::toDoctrine($request);
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function countAll(): int
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('COUNT(r.id)')
            ->from(DoctrineRequestInformationEntity::class, 'r');
        return (int) $qb->getQuery()->getSingleScalarResult();
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

}
