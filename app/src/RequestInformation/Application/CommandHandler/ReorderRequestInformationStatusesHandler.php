<?php

namespace App\RequestInformation\Application\CommandHandler;

use App\RequestInformation\Application\Command\ReorderRequestInformationStatusesCommand;
use App\RequestInformation\Domain\Repository\RequestInformationStatusRepositoryInterface;

final class ReorderRequestInformationStatusesHandler
{
    public function __construct(
        private readonly RequestInformationStatusRepositoryInterface $repo
    ) {}

    public function __invoke(ReorderRequestInformationStatusesCommand $command): void
    {
        $orgId = $command->getOrganizationId();
        $items = $command->getItems(); // [['id' => string, 'sort' => int], ...]

        if (!\is_array($items) || empty($items)) {
            throw new \InvalidArgumentException('Items must be a non-empty array.');
        }

        // Opción A (genérica): cargar uno por uno y guardar
        foreach ($items as $row) {
            if (!isset($row['id'], $row['sort'])) {
                throw new \InvalidArgumentException('Each item must have id and sort.');
            }

            $status = $this->repo->findByIdAndOrganizationId((string)$row['id'], $orgId);
            if (!$status) {
                // Puedes decidir: saltar, o lanzar excepción
                throw new \DomainException(sprintf('Status %s not found for this organization.', $row['id']));
            }

            $status->setSort((int)$row['sort']);
            $this->repo->save($status);
        }

        // Opción B (performante): si tu repo tiene un método batch, úsalo en su lugar.
        // $this->repo->bulkUpdateSort($orgId, $items);
    }
}
