<?php

namespace App\Activation\Infrastructure\Persistence;

use App\Activation\Domain\Aggregate\Activation;

class ActivationMapper
{
    public static function toDomain(DoctrineActivationEntity $entity): Activation
    {
        $activation = new Activation(
            $entity->getId(),
            $entity->getTitle(),
            $entity->getDescription(),
            $entity->getType(),
            $entity->getPriority(),
            $entity->getChannels(),
            $entity->getOrganizationId(),
            $entity->getCreatedBy(),
            $entity->getTargetAudience(),
            $entity->getScheduledFor()
        );

        // Use reflection to set the created and updated dates and status
        $reflection = new \ReflectionClass($activation);
        
        $statusProperty = $reflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($activation, $entity->getStatus());
        
        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($activation, $entity->getCreatedAt());
        
        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($activation, $entity->getUpdatedAt());

        return $activation;
    }

    public static function toEntity(Activation $activation): DoctrineActivationEntity
    {
        return new DoctrineActivationEntity(
            $activation->getId(),
            $activation->getTitle(),
            $activation->getDescription(),
            $activation->getType(),
            $activation->getStatus(),
            $activation->getPriority(),
            $activation->getChannels(),
            $activation->getOrganizationId(),
            $activation->getCreatedBy(),
            $activation->getCreatedAt(),
            $activation->getTargetAudience(),
            $activation->getScheduledFor(),
            $activation->getUpdatedAt()
        );
    }

    public static function updateEntity(DoctrineActivationEntity $entity, Activation $activation): void
    {
        $entity->setTitle($activation->getTitle());
        $entity->setDescription($activation->getDescription());
        $entity->setType($activation->getType());
        $entity->setStatus($activation->getStatus());
        $entity->setPriority($activation->getPriority());
        $entity->setChannels($activation->getChannels());
        $entity->setTargetAudience($activation->getTargetAudience());
        $entity->setScheduledFor($activation->getScheduledFor());
        $entity->setUpdatedAt($activation->getUpdatedAt());
    }
}