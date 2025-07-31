<?php

namespace App\Quotation\Application\Command;

class CreateQuotationCommand
{
    public function __construct(
        public readonly string $requestInformationId,
        public readonly array $details, // Array de detalles [{description, unitPrice, quantity, total}]
        public readonly ?string $status = null
    ) {}
}

