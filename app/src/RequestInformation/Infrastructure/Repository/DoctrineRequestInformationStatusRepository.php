<?php

namespace App\RequestInformation\Infrastructure\Repository;

use App\RequestInformation\Domain\Entity\RequestInformationStatus;
use App\RequestInformation\Domain\Repository\RequestInformationStatusRepositoryInterface;
use App\RequestInformation\Infrastructure\Persistence\DoctrineRequestInformationStatusEntity;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineRequestInformationStatusRepository implements RequestInformationStatusRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByOrganizationId(string $organizationId): array
    {
        $entity = $this->em->getRepository(DoctrineRequestInformationStatusEntity::class)
            ->findBy(['organizationId' => $organizationId]);
        return $entity;
    }

    public function findByCode(string $code): ?RequestInformationStatus
    {
        $entity = $this->em
            ->getRepository(DoctrineRequestInformationStatusEntity::class)
            ->findOneBy(['code' => $code]);

        return $entity ? $this->mapToDomain($entity) : null;
    }

    public function findById(string $id): ?RequestInformationStatus
    {
        $entity = $this->em
            ->getRepository(DoctrineRequestInformationStatusEntity::class)
            ->find($id);

        return $entity ? $this->mapToDomain($entity) : null;
    }

    private function mapToDomain(DoctrineRequestInformationStatusEntity $entity): RequestInformationStatus
    {
        return new RequestInformationStatus(
            $entity->getId(),
            $entity->getCode(),
            $entity->getName(),
            $entity->isDefault(),
            $entity->getOrganization()
        );
    }

    public function findDefault(): ?RequestInformationStatus
    {
        $entity = $this->em
            ->getRepository(DoctrineRequestInformationStatusEntity::class)
            ->findOneBy(['isDefault' => true]);

        return $entity ? $this->mapToDomain($entity) : null;
    }

    public function save(RequestInformationStatus $request): RequestInformationStatus
    {
        $entity = null;
        if ($request->getId()) {
            $entity = $this->em->getRepository(DoctrineRequestInformationStatusEntity::class)
                ->find($request->getId());
        } else {
            $entity = new DoctrineRequestInformationStatusEntity(
                $request->getCode(),
                $request->getName(),
                $request->getIsDefault(),
                $request->getOrganizationId()
            );
        }
        $this->em->persist($entity);
        $this->em->flush();
        return $entity;
    }
}
