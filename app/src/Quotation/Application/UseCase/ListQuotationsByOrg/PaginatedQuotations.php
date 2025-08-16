<?php

namespace App\Quotation\Application\UseCase\ListQuotationsByOrg;

use App\Quotation\Domain\Aggregate\Quotation;

/**
 * Resultado paginado para quotations.
 */
class PaginatedQuotations
{
    /** @var Quotation[] */
    public array $items;
    public int $total;
    public int $page;
    public int $perPage;
    public int $pages;

    /**
     * @param Quotation[] $items
     */
    public function __construct(array $items, int $total, int $page, int $perPage)
    {
        $this->items   = $items;
        $this->total   = $total;
        $this->page    = $page;
        $this->perPage = $perPage;
        $this->pages   = (int) \ceil($total / max(1, $perPage));
    }
}
