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

        return new PotentialCustomer(
            $entity->getId(),
            $entity->getFirstName(),
            $entity->getLastName(),
            $emails,
            $entity->getPhone(),
            $entity->getCity()
        );
    }

    /**
     * Convierte de Aggregate de Dominio a Doctrine Entity
     * Si el $entity es null, crea uno nuevo.
     */
    public static function toDoctrine(
        PotentialCustomer $domain,
        DoctrinePotentialCustomerEntity $entity = null
    ): DoctrinePotentialCustomerEntity {
        if (!$entity) {
            $entity = new DoctrinePotentialCustomerEntity();
        }

        // Si el id es null, Doctrine lo autogenera
        $entity->setFirstName($domain->getFirstName())
            ->setLastName($domain->getLastName())
            ->setPhone($domain->getPhone())
            ->setCity($domain->getCity());

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
}
