<?php

namespace App\Quotation\Application\Query;

use App\Quotation\Application\UseCase\ListQuotationsByOrg\PaginatedQuotations;

interface QuotationQueryServiceInterface
{
    public function findByOrganizationPaginated(
        string $organizationId,
        int $page = 1,
        int $perPage = 20,
        ?string $orderBy = 'createdAt',
        string $direction = 'DESC'
    ): PaginatedQuotations;
}
