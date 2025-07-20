<?php

namespace App\RequestInformation\Infrastructure\Persistence;

use App\RequestInformation\Domain\Aggregate\RequestInformation;
use App\RequestInformation\Domain\ValueObject\Email;
use App\RequestInformation\Domain\ValueObject\Phone;
use Ramsey\Uuid\Uuid;

class RequestInformationMapper
{
    public static function toDoctrine(RequestInformation $domain): DoctrineRequestInformationEntity
    {
        $entity = new DoctrineRequestInformationEntity();
        $entity->setProgramInterestId($domain->getProgramInterestId())
            ->setLeadOriginId($domain->getLeadOriginId())
            ->setFirstName($domain->getFirstName())
            ->setLastName($domain->getLastName())
            ->setStatus($domain->getStatus())
            ->setEmail((string)$domain->getEmail())
            ->setPhone((string)$domain->getPhone())
            ->setCity($domain->getCity());
        return $entity;
    }

    public static function toDomain(DoctrineRequestInformationEntity $entity): RequestInformation
    {
        return new RequestInformation(
            $entity->getId(),
            $entity->getProgramInterestId(),
            $entity->getLeadOriginId(),
            $entity->getStatus(),
            $entity->getFirstName(),
            $entity->getLastName(),
            new Email($entity->getEmail()),
            new Phone($entity->getPhone()),
            $entity->getCity(),
        );
    }
}
