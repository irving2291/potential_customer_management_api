<?php

namespace App\RequestInformation\Application\CommandHandler;

use App\RequestInformation\Application\Command\UpdateRequestInformationStatusCommand;
use App\RequestInformation\Domain\Repository\RequestInformationStatusRepositoryInterface;

final class UpdateRequestInformationStatusHandler
{
    public function __construct(
        private readonly RequestInformationStatusRepositoryInterface $repo
    ) {}

    public function __invoke(UpdateRequestInformationStatusCommand $command): void
    {
        // Buscar el estado por id + organización (evitar fuga multitenant)
        $status = $this->repo->findByIdAndOrganizationId(
            $command->getId(),
            $command->getOrganizationId()
        );

        if (!$status) {
            throw new \DomainException('Request status not found.');
        }

        // Actualizar solo los campos provistos (parciales)
        if (null !== $command->getCode()) {
            $status->setCode($command->getCode());
        }
        if (null !== $command->getName()) {
            $status->setName($command->getName());
        }
        if (null !== $command->getSort()) {
            $status->setSort((int) $command->getSort());
        }

        // Persistir
        $this->repo->save($status);
    }
}
