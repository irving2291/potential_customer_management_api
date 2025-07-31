<?php

namespace App\Quotation\Application\Command;

class RemoveQuotationDetailCommand
{
    public function __construct(
        public readonly string $quotationId,
        public readonly int $index
    ) {}
}
