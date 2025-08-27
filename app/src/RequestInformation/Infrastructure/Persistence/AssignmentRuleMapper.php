<?php

namespace App\RequestInformation\Infrastructure\Persistence;

use App\RequestInformation\Domain\Aggregate\AssignmentRule;

class AssignmentRuleMapper
{
    public static function toDomain(DoctrineAssignmentRuleEntity $entity): AssignmentRule
    {
        $rule = new AssignmentRule(
            $entity->getId(),
            $entity->getName(),
            $entity->getDescription(),
            $entity->isActive(),
            $entity->getPriority(),
            $entity->getConditions(),
            $entity->getAssignmentType(),
            $entity->getAssigneeIds(),
            $entity->getOrganizationId()
        );

        // Set the created and updated dates using reflection since they're private
        $reflection = new \ReflectionClass($rule);
        
        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($rule, $entity->getCreatedAt());
        
        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($rule, $entity->getUpdatedAt());

        return $rule;
    }

    public static function toEntity(AssignmentRule $rule): DoctrineAssignmentRuleEntity
    {
        return new DoctrineAssignmentRuleEntity(
            $rule->getId(),
            $rule->getName(),
            $rule->getDescription(),
            $rule->isActive(),
            $rule->getPriority(),
            $rule->getConditions(),
            $rule->getAssignmentType(),
            $rule->getAssigneeIds(),
            $rule->getOrganizationId(),
            $rule->getCreatedAt(),
            $rule->getUpdatedAt()
        );
    }

    public static function updateEntity(DoctrineAssignmentRuleEntity $entity, AssignmentRule $rule): void
    {
        $entity->updateRule(
            $rule->getName(),
            $rule->getDescription(),
            $rule->isActive(),
            $rule->getPriority(),
            $rule->getConditions(),
            $rule->getAssignmentType(),
            $rule->getAssigneeIds()
        );
    }
}