<?php
namespace App\RequestInformation\Domain\Repository;


use App\RequestInformation\Domain\Aggregate\RequestInformation;

interface RequestInformationRepositoryInterface
{
    public function findById(string $id): ?RequestInformation;

    public function save(RequestInformation $request): RequestInformation;

    public function existsByEmailProgramAndLeadInThreeMonth(string $email, string $programId, string $leadId): bool;

    public function getAllPaginated(?string $status, int $page, int $limit): array;

    public function getSummaryByDates(\DateTimeInterface $from, \DateTimeInterface $to): array;

    public function findByAssigneeId(string $assigneeId, ?string $status = null, int $page = 1, int $limit = 10): array;

    public function findLastAssignedByRule(string $ruleId): ?RequestInformation;

    public function countActiveByAssignee(string $assigneeId, string $organizationId): int;

    public function findByAssigneeIdAndOrganization(string $assigneeId, string $organizationId, ?string $status = null): array;
}
