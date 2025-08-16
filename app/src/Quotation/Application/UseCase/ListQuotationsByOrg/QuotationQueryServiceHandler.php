<?php

namespace App\Quotation\Application\UseCase\ListQuotationsByOrg;

use App\Quotation\Application\Query\QuotationQueryServiceInterface;
use App\Quotation\Domain\Aggregate\Quotation;
use App\Quotation\Domain\Repository\QuotationRepositoryInterface;

readonly class QuotationQueryServiceHandler implements QuotationQueryServiceInterface
{
    public function __construct(
        private QuotationRepositoryInterface $quotationRepository
    ) {}

    public function findByOrganizationPaginated(
        string $organizationId,
        int $page = 1,
        int $perPage = 20,
        ?string $orderBy = 'createdAt',
        string $direction = 'DESC'
    ): PaginatedQuotations {
        // Normalizamos parámetros
        $page    = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $direction = \strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        // Opcional: whitelist de campos ordenables a nivel de dominio
        $allowedOrder = ['createdAt', 'updatedAt', 'status', 'requestInformationId'];
        if ($orderBy === null || !\in_array($orderBy, $allowedOrder, true)) {
            $orderBy = 'createdAt';
        }

        /**
         * Importante: este método del repositorio debe devolver:
         * [
         *   'items' => Quotation[],   // del dominio
         *   'total' => int,
         *   'page' => int,
         *   'perPage' => int,
         *   'pages' => int            // puede omitirse; lo recalculamos
         * ]
         *
         * Si tu implementación actual del repositorio se llama distinto
         * (p. ej. paginateByOrgId), ajusta la llamada aquí.
         */
        $result = $this->quotationRepository->paginateByOrgId(
            $organizationId,
            $page,
            $perPage,
            $orderBy,
            $direction
        );

        // Aseguramos tipos y valores por defecto
        /** @var Quotation[] $items */
        $items = \is_array($result['items'] ?? null) ? $result['items'] : [];
        $total = (int) ($result['total'] ?? 0);
        $page  = (int) ($result['page'] ?? $page);
        $pp    = (int) ($result['perPage'] ?? $perPage);

        return new PaginatedQuotations($items, $total, $page, $pp);
    }
}
