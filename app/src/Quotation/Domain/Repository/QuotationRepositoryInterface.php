<?php

namespace App\Quotation\Domain\Repository;

use App\Quotation\Domain\Aggregate\Quotation;

interface QuotationRepositoryInterface
{
    public function save(Quotation $quotation): void;
    public function findById(string $id): ?Quotation;
    public function findByRequestInformationId(string $requestInformationId): ?Quotation;

    public function existsActiveQuotationForRequest(string $requestInformationId, array $excludedStatuses = []): bool;

}
