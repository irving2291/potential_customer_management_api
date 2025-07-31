<?php

namespace App\Quotation\Application\Command;

class AddQuotationDetailCommand
{
    public function __construct(
        public readonly string $quotationId,
        public readonly string $description,
        public readonly float $unitPrice,
        public readonly int $quantity,
        public readonly float $total
    ) {}
}
