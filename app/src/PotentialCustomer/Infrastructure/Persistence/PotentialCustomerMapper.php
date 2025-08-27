<?php

namespace App\PotentialCustomer\Infrastructure\Persistence;

use App\PotentialCustomer\Domain\Aggregate\PotentialCustomer;
use App\PotentialCustomer\Domain\Entity\Email;

class PotentialCustomerMapper
{
    /**
     * Convierte de Doctrine Entity a Aggregate de Dominio
     */
    public static function toDomain(DoctrinePotentialCustomerEntity $entity): PotentialCustomer
    {
        $emails = [];
        foreach ($entity->getEmails() as $emailEntity) {
            $emails[] = new Email(
                $emailEntity->getValue(),
                $emailEntity->getRegisteredAt()
            );
        }

        $customer = new PotentialCustomer(
            $entity->getId(),
            $entity->getType(),
            $entity->getOrganizationId(),
            $emails,
            $entity->getPhone() ?? '',
            $entity->getPriority(),
            $entity->getFirstName(),
            $entity->getLastName(),
            $entity->getCompanyName(),
            $entity->getCity(),
            $entity->getAssignedTo(),
            $entity->getAssignedToName()
        );

        // Use reflection to set private properties that don't have setters in constructor
        $reflection = new \ReflectionClass($customer);
        
        $statusProperty = $reflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($customer, $entity->getStatus());

        $addressProperty = $reflection->getProperty('address');
        $addressProperty->setAccessible(true);
        $addressProperty->setValue($customer, $entity->getAddress());

        $countryProperty = $reflection->getProperty('country');
        $countryProperty->setAccessible(true);
        $countryProperty->setValue($customer, $entity->getCountry());

        $websiteProperty = $reflection->getProperty('website');
        $websiteProperty->setAccessible(true);
        $websiteProperty->setValue($customer, $entity->getWebsite());

        $industryProperty = $reflection->getProperty('industry');
        $industryProperty->setAccessible(true);
        $industryProperty->setValue($customer, $entity->getIndustry());

        $tagsProperty = $reflection->getProperty('tags');
        $tagsProperty->setAccessible(true);
        $tagsProperty->setValue($customer, $entity->getTags());

        $totalValueProperty = $reflection->getProperty('totalValue');
        $totalValueProperty->setAccessible(true);
        $totalValueProperty->setValue($customer, $entity->getTotalValue());

        $potentialValueProperty = $reflection->getProperty('potentialValue');
        $potentialValueProperty->setAccessible(true);
        $potentialValueProperty->setValue($customer, $entity->getPotentialValue());

        $lastContactDateProperty = $reflection->getProperty('lastContactDate');
        $lastContactDateProperty->setAccessible(true);
        $lastContactDateProperty->setValue($customer, $entity->getLastContactDate());

        $requestsCountProperty = $reflection->getProperty('requestsCount');
        $requestsCountProperty->setAccessible(true);
        $requestsCountProperty->setValue($customer, $entity->getRequestsCount());

        $quotationsCountProperty = $reflection->getProperty('quotationsCount');
        $quotationsCountProperty->setAccessible(true);
        $quotationsCountProperty->setValue($customer, $entity->getQuotationsCount());

        $conversationsCountProperty = $reflection->getProperty('conversationsCount');
        $conversationsCountProperty->setAccessible(true);
        $conversationsCountProperty->setValue($customer, $entity->getConversationsCount());

        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($customer, $entity->getCreatedAt());

        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($customer, $entity->getUpdatedAt());

        $convertedAtProperty = $reflection->getProperty('convertedAt');
        $convertedAtProperty->setAccessible(true);
        $convertedAtProperty->setValue($customer, $entity->getConvertedAt());

        return $customer;
    }

    /**
     * Convierte de Aggregate de Dominio a Doctrine Entity
     */
    public static function toDoctrine(
        PotentialCustomer $domain,
        DoctrinePotentialCustomerEntity $entity = null
    ): DoctrinePotentialCustomerEntity {
        if (!$entity) {
            $entity = new DoctrinePotentialCustomerEntity();
        }

        $entity->setId($domain->getId())
            ->setType($domain->getType())
            ->setStatus($domain->getStatus())
            ->setFirstName($domain->getFirstName())
            ->setLastName($domain->getLastName())
            ->setCompanyName($domain->getCompanyName())
            ->setPhone($domain->getPhone())
            ->setAddress($domain->getAddress())
            ->setCity($domain->getCity())
            ->setCountry($domain->getCountry())
            ->setWebsite($domain->getWebsite())
            ->setIndustry($domain->getIndustry())
            ->setTags($domain->getTags())
            ->setPriority($domain->getPriority())
            ->setAssignedTo($domain->getAssignedTo())
            ->setAssignedToName($domain->getAssignedToName())
            ->setTotalValue($domain->getTotalValue())
            ->setPotentialValue($domain->getPotentialValue())
            ->setLastContactDate($domain->getLastContactDate())
            ->setRequestsCount($domain->getRequestsCount())
            ->setQuotationsCount($domain->getQuotationsCount())
            ->setConversationsCount($domain->getConversationsCount())
            ->setOrganizationId($domain->getOrganizationId())
            ->setCreatedAt($domain->getCreatedAt())
            ->setUpdatedAt($domain->getUpdatedAt())
            ->setConvertedAt($domain->getConvertedAt());

        // Limpiamos los emails actuales (para sincronizar correctamente)
        foreach ($entity->getEmails() as $oldEmail) {
            $entity->getEmails()->removeElement($oldEmail);
        }

        // Agregamos los emails del dominio a la entidad Doctrine
        foreach ($domain->getEmails() as $emailVO) {
            $emailEntity = new DoctrineEmailEntity();
            $emailEntity->setValue($emailVO->getValue());
            $emailEntity->setRegisteredAt($emailVO->getRegisteredAt());
            $emailEntity->setPotentialCustomer($entity);

            $entity->addEmail($emailEntity);
        }

        return $entity;
    }

    /**
     * Updates an existing entity with domain data
     */
    public static function updateEntity(DoctrinePotentialCustomerEntity $entity, PotentialCustomer $domain): void
    {
        $entity->setType($domain->getType())
            ->setStatus($domain->getStatus())
            ->setFirstName($domain->getFirstName())
            ->setLastName($domain->getLastName())
            ->setCompanyName($domain->getCompanyName())
            ->setPhone($domain->getPhone())
            ->setAddress($domain->getAddress())
            ->setCity($domain->getCity())
            ->setCountry($domain->getCountry())
            ->setWebsite($domain->getWebsite())
            ->setIndustry($domain->getIndustry())
            ->setTags($domain->getTags())
            ->setPriority($domain->getPriority())
            ->setAssignedTo($domain->getAssignedTo())
            ->setAssignedToName($domain->getAssignedToName())
            ->setTotalValue($domain->getTotalValue())
            ->setPotentialValue($domain->getPotentialValue())
            ->setLastContactDate($domain->getLastContactDate())
            ->setRequestsCount($domain->getRequestsCount())
            ->setQuotationsCount($domain->getQuotationsCount())
            ->setConversationsCount($domain->getConversationsCount())
            ->setUpdatedAt($domain->getUpdatedAt())
            ->setConvertedAt($domain->getConvertedAt());

        // Update emails
        foreach ($entity->getEmails() as $oldEmail) {
            $entity->getEmails()->removeElement($oldEmail);
        }

        foreach ($domain->getEmails() as $emailVO) {
            $emailEntity = new DoctrineEmailEntity();
            $emailEntity->setValue($emailVO->getValue());
            $emailEntity->setRegisteredAt($emailVO->getRegisteredAt());
            $emailEntity->setPotentialCustomer($entity);

            $entity->addEmail($emailEntity);
        }
    }
}
