<?php

namespace App\Assignee\Infrastructure\Persistence;

use App\Assignee\Domain\Aggregate\Assignee;

class AssigneeMapper
{
    public static function toDomain(DoctrineAssigneeEntity $entity): Assignee
    {
        $assignee = new Assignee(
            $entity->getId(),
            $entity->getFirstName(),
            $entity->getLastName(),
            $entity->getEmail(),
            $entity->getPhone(),
            $entity->getAvatar() ?? '',
            $entity->isActive(),
            $entity->getRole(),
            $entity->getDepartment(),
            $entity->getOrganizationId(),
            $entity->getUserId()
        );

        // Use reflection to set the created and updated dates
        $reflection = new \ReflectionClass($assignee);
        
        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($assignee, $entity->getCreatedAt());
        
        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($assignee, $entity->getUpdatedAt());

        return $assignee;
    }

    public static function toEntity(Assignee $assignee): DoctrineAssigneeEntity
    {
        return new DoctrineAssigneeEntity(
            $assignee->getId(),
            $assignee->getFirstName(),
            $assignee->getLastName(),
            $assignee->getEmail(),
            $assignee->getPhone(),
            $assignee->getAvatar(),
            $assignee->isActive(),
            $assignee->getRole(),
            $assignee->getDepartment(),
            $assignee->getOrganizationId(),
            $assignee->getUserId(),
            $assignee->getCreatedAt(),
            $assignee->getUpdatedAt()
        );
    }

    public static function updateEntity(DoctrineAssigneeEntity $entity, Assignee $assignee): void
    {
        $entity->setFirstName($assignee->getFirstName());
        $entity->setLastName($assignee->getLastName());
        $entity->setEmail($assignee->getEmail());
        $entity->setPhone($assignee->getPhone());
        $entity->setAvatar($assignee->getAvatar());
        $entity->setActive($assignee->isActive());
        $entity->setRole($assignee->getRole());
        $entity->setDepartment($assignee->getDepartment());
        $entity->setUpdatedAt($assignee->getUpdatedAt());
    }
}