<?php

namespace App\Quotation\Domain\ValueObject;

class QuotationDetail
{
    public function __construct(
        public string $description,
        public float $unitPrice,
        public int $quantity,
        public float $total
    ) {}
}
