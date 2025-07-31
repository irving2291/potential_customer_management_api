<?php

namespace App\Quotation\Application\Command;

class EditQuotationDetailCommand
{
    public function __construct(
        public readonly string $quotationId,
        public readonly int $index,
        public readonly string $description,
        public readonly float $unitPrice,
        public readonly int $quantity,
        public readonly float $total
    ) {}
}
