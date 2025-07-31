<?php

namespace App\Quotation\Application\Command;

class ChangeQuotationStatusCommand
{
    public function __construct(
        public readonly string $quotationId,
        public readonly string $newStatus
    ) {}
}
