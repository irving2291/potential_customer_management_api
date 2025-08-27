<?php

namespace App\RequestInformation\Domain\Service;

use App\RequestInformation\Domain\Aggregate\AssignmentRule;
use App\RequestInformation\Domain\Aggregate\RequestInformation;
use App\RequestInformation\Domain\Repository\AssignmentRuleRepositoryInterface;
use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use App\Assignee\Domain\Repository\AssigneeRepositoryInterface;

class AssignmentService
{
    private AssignmentRuleRepositoryInterface $assignmentRuleRepository;
    private RequestInformationRepositoryInterface $requestRepository;
    private AssigneeRepositoryInterface $assigneeRepository;

    public function __construct(
        AssignmentRuleRepositoryInterface $assignmentRuleRepository,
        RequestInformationRepositoryInterface $requestRepository,
        AssigneeRepositoryInterface $assigneeRepository
    ) {
        $this->assignmentRuleRepository = $assignmentRuleRepository;
        $this->requestRepository = $requestRepository;
        $this->assigneeRepository = $assigneeRepository;
    }

    public function assignResponsible(RequestInformation $request): ?string
    {
        $organizationId = $request->getOrganizationId();
        $rules = $this->assignmentRuleRepository->findActiveByOrganizationIdOrderedByPriority($organizationId);

        if (empty($rules)) {
            return null;
        }

        $requestData = $this->buildRequestData($request);

        foreach ($rules as $rule) {
            if ($rule->matchesConditions($requestData)) {
                $assigneeId = $this->selectAssignee($rule, $organizationId);
                if ($assigneeId) {
                    return $assigneeId;
                }
            }
        }

        return null;
    }

    private function buildRequestData(RequestInformation $request): array
    {
        return [
            'programInterestId' => $request->getProgramInterestId(),
            'leadOriginId' => $request->getLeadOriginId(),
            'organizationId' => $request->getOrganizationId(),
            'firstName' => $request->getFirstName(),
            'lastName' => $request->getLastName(),
            'email' => $request->getEmail()->getValue(),
            'phone' => $request->getPhone()->getValue(),
            'city' => $request->getCity(),
            'status' => $request->getStatus()->getCode(),
        ];
    }

    private function selectAssignee(AssignmentRule $rule, string $organizationId): ?string
    {
        $assignmentContext = $this->buildAssignmentContext($rule, $organizationId);
        return $rule->getNextAssignee($assignmentContext);
    }

    private function buildAssignmentContext(AssignmentRule $rule, string $organizationId): array
    {
        $context = [];

        switch ($rule->getAssignmentType()) {
            case 'round_robin':
                $context['lastAssigneeIndex'] = $this->getLastAssigneeIndex($rule);
                break;

            case 'load_balanced':
                $context['assigneeLoads'] = $this->getAssigneeLoads($rule->getAssigneeIds(), $organizationId);
                break;

            case 'skill_based':
                $context['requiredSkills'] = $this->getRequiredSkills($rule);
                $context['assigneeSkills'] = $this->getAssigneeSkills($rule->getAssigneeIds());
                break;
        }

        return $context;
    }

    private function getLastAssigneeIndex(AssignmentRule $rule): int
    {
        // Get the last assigned request for this rule to determine round-robin position
        // This is a simplified implementation - in production you might want to store this in cache or database
        $assigneeIds = $rule->getAssigneeIds();
        $lastRequest = $this->requestRepository->findLastAssignedByRule($rule->getId());
        
        if (!$lastRequest || !$lastRequest->getAssigneeId()) {
            return -1;
        }

        $lastAssigneeId = $lastRequest->getAssigneeId();
        $index = array_search($lastAssigneeId, $assigneeIds);
        
        return $index !== false ? $index : -1;
    }

    private function getAssigneeLoads(array $assigneeIds, string $organizationId): array
    {
        $loads = [];
        
        foreach ($assigneeIds as $assigneeId) {
            // Count active requests assigned to this assignee
            $loads[$assigneeId] = $this->requestRepository->countActiveByAssignee($assigneeId, $organizationId);
        }
        
        return $loads;
    }

    private function getRequiredSkills(AssignmentRule $rule): array
    {
        // Extract required skills from rule conditions or metadata
        // This is a simplified implementation
        $conditions = $rule->getConditions();
        $skills = [];
        
        foreach ($conditions as $condition) {
            if (isset($condition['field']) && $condition['field'] === 'required_skills') {
                $skills = is_array($condition['value']) ? $condition['value'] : [$condition['value']];
                break;
            }
        }
        
        return $skills;
    }

    private function getAssigneeSkills(array $assigneeIds): array
    {
        $skills = [];
        
        foreach ($assigneeIds as $assigneeId) {
            $assignee = $this->assigneeRepository->findById($assigneeId);
            if ($assignee) {
                // In a real implementation, you might have a skills field or related entity
                // For now, we'll use department as a proxy for skills
                $skills[$assigneeId] = [$assignee->getDepartment(), $assignee->getRole()];
            }
        }
        
        return $skills;
    }

    public function reassignRequest(RequestInformation $request, string $newAssigneeId): void
    {
        // Validate that the assignee exists and is active
        $assignee = $this->assigneeRepository->findById($newAssigneeId);
        if (!$assignee || !$assignee->isActive()) {
            throw new \DomainException('Assignee not found or inactive');
        }

        // Validate that the assignee belongs to the same organization
        if ($assignee->getOrganizationId() !== $request->getOrganizationId()) {
            throw new \DomainException('Assignee does not belong to the same organization');
        }

        $request->assignTo($newAssigneeId);
    }

    public function unassignRequest(RequestInformation $request): void
    {
        $request->unassign();
    }
}