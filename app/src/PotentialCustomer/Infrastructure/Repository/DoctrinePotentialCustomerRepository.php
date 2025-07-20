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

    public function save(PotentialCustomer $customer): void
    {
        // Puedes buscar si existe, para update, o simplemente persistir (Doctrine decide si es nuevo o update)
        $entity = PotentialCustomerMapper::toDoctrine($customer);
        $this->em->persist($entity);
        $this->em->flush();
    }
}
