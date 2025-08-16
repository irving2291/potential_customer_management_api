<?php

namespace App\Quotation\Domain\Repository;

use App\Quotation\Domain\Aggregate\Quotation;

interface QuotationRepositoryInterface
{
    public function save(Quotation $quotation): void;
    public function findById(string $id): ?Quotation;

    public function paginateByOrgId(
        string $orgId,
        int $page = 1,
        int $perPage = 20,
        ?string $orderBy = 'createdAt',
        string $direction = 'DESC'
    ): array;
    public function findByRequestInformationId(string $requestInformationId): ?Quotation;

    public function existsActiveQuotationForRequest(string $requestInformationId, array $excludedStatuses = []): bool;

}
