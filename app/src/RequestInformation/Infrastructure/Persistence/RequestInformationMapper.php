<?php

namespace App\RequestInformation\Infrastructure\Persistence;

use App\RequestInformation\Domain\Aggregate\RequestInformation;
use App\RequestInformation\Domain\Entity\RequestInformationStatus;
use App\RequestInformation\Domain\ValueObject\Email;
use App\RequestInformation\Domain\ValueObject\Phone;

class RequestInformationMapper
{
    public static function toDoctrine(
        RequestInformation $domain,
        DoctrineRequestInformationStatusEntity $statusEntity,
        ?DoctrineRequestInformationEntity $entity = null
    ): DoctrineRequestInformationEntity
    {
        if (!$entity) {
            $entity = new DoctrineRequestInformationEntity();
        }
        $entity->setProgramInterestId($domain->getProgramInterestId())
            ->setOrganizationId($domain->getOrganizationId())
            ->setLeadOriginId($domain->getLeadOriginId())
            ->setFirstName($domain->getFirstName())
            ->setLastName($domain->getLastName())
            ->setStatus($statusEntity)
            ->setEmail((string)$domain->getEmail())
            ->setPhone((string)$domain->getPhone())
            ->setCity($domain->getCity())
            ->setAssigneeId($domain->getAssigneeId());
        return $entity;
    }

    public static function toDomain(DoctrineRequestInformationEntity $entity): RequestInformation
    {
        $statusEntity = $entity->getStatus();

        return new RequestInformation(
            $entity->getId(),
            $entity->getProgramInterestId(),
            $entity->getLeadOriginId(),
            $entity->getOrganizationId(),
            new RequestInformationStatus(
                $statusEntity->getId(),
                $statusEntity->getCode(),
                $statusEntity->getName(),
                $statusEntity->isDefault(),
                $statusEntity->getOrganization(),
                $statusEntity->getSort()
            ),
            $entity->getFirstName(),
            $entity->getLastName(),
            new Email($entity->getEmail()),
            new Phone($entity->getPhone()),
            $entity->getCity(),
            $entity->getAssigneeId()
        );
    }
}
