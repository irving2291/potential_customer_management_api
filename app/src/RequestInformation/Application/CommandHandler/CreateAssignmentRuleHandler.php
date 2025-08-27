<?php

namespace App\RequestInformation\Application\CommandHandler;

use App\RequestInformation\Application\Command\CreateAssignmentRuleCommand;
use App\RequestInformation\Domain\Aggregate\AssignmentRule;
use App\RequestInformation\Domain\Repository\AssignmentRuleRepositoryInterface;
use App\Assignee\Domain\Repository\AssigneeRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class CreateAssignmentRuleHandler
{
    public function __construct(
        private AssignmentRuleRepositoryInterface $assignmentRuleRepository,
        private AssigneeRepositoryInterface $assigneeRepository
    ) {}

    public function __invoke(CreateAssignmentRuleCommand $command): string
    {
        try {
            // Validate that rule name is unique within organization
            if ($this->assignmentRuleRepository->existsByNameAndOrganization($command->name, $command->organizationId)) {
                throw new \DomainException('Assignment rule with this name already exists in the organization');
            }

            // Validate assignment type
            $validTypes = ['round_robin', 'load_balanced', 'skill_based', 'manual', 'random'];
            if (!in_array($command->assignmentType, $validTypes)) {
                throw new \DomainException('Invalid assignment type. Valid types: ' . implode(', ', $validTypes));
            }

            // Validate that all assignees exist and belong to the organization
            foreach ($command->assigneeIds as $assigneeId) {
                $assignee = $this->assigneeRepository->findById($assigneeId);
                if (!$assignee) {
                    throw new \DomainException("Assignee with ID {$assigneeId} not found");
                }
                if ($assignee->getOrganizationId() !== $command->organizationId) {
                    throw new \DomainException("Assignee {$assigneeId} does not belong to the organization");
                }
                if (!$assignee->isActive()) {
                    throw new \DomainException("Assignee {$assigneeId} is not active");
                }
            }

            // Validate conditions structure
            $this->validateConditions($command->conditions);

            // Create the assignment rule
            $ruleId = uuid_create(UUID_TYPE_RANDOM);
            $assignmentRule = new AssignmentRule(
                $ruleId,
                $command->name,
                $command->description,
                $command->active,
                $command->priority,
                $command->conditions,
                $command->assignmentType,
                $command->assigneeIds,
                $command->organizationId
            );

            $this->assignmentRuleRepository->save($assignmentRule);

            return $ruleId;

        } catch (\DomainException $exception) {
            throw new UnrecoverableMessageHandlingException($exception->getMessage(), 0, $exception);
        } catch (\Throwable $e) {
            throw new UnrecoverableMessageHandlingException('Error creating assignment rule: ' . $e->getMessage(), 0, $e);
        }
    }

    private function validateConditions(array $conditions): void
    {
        $validFields = [
            'programInterestId', 'leadOriginId', 'city', 'firstName', 'lastName', 
            'email', 'phone', 'status', 'amount', 'required_skills'
        ];
        
        $validOperators = [
            'equals', 'not_equals', 'contains', 'not_contains', 
            'greater_than', 'less_than', 'greater_equal', 'less_equal',
            'in', 'not_in'
        ];

        foreach ($conditions as $condition) {
            if (!is_array($condition)) {
                throw new \DomainException('Each condition must be an array');
            }

            if (!isset($condition['field'], $condition['operator'], $condition['value'])) {
                throw new \DomainException('Each condition must have field, operator, and value');
            }

            if (!in_array($condition['field'], $validFields)) {
                throw new \DomainException("Invalid condition field: {$condition['field']}. Valid fields: " . implode(', ', $validFields));
            }

            if (!in_array($condition['operator'], $validOperators)) {
                throw new \DomainException("Invalid condition operator: {$condition['operator']}. Valid operators: " . implode(', ', $validOperators));
            }
        }
    }
}