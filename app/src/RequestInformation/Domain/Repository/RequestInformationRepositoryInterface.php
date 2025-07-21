<?php
namespace App\RequestInformation\Domain\Repository;


use App\RequestInformation\Domain\Aggregate\RequestInformation;

interface RequestInformationRepositoryInterface
{
    public function save(RequestInformation $request): void;

    public function existsByEmailProgramAndLeadInThreeMonth(string $email, string $programId, string $leadId): bool;

    public function findByStatusPaginated(string $status, int $page, int $limit): array;

    public function getSummaryByDates(\DateTimeInterface $from, \DateTimeInterface $to): array;
}
