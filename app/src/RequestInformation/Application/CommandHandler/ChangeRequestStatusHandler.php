<?php

namespace App\RequestInformation\Application\CommandHandler;

use App\RequestInformation\Application\Command\ChangeRequestStatusCommand;
use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use App\RequestInformation\Domain\Repository\RequestInformationStatusRepositoryInterface; // <-- Nuevo: interface para el repo de status
use DomainException;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ChangeRequestStatusHandler
{
    public function __construct(
        private RequestInformationRepositoryInterface $repo,
        private RequestInformationStatusRepositoryInterface $statusRepo
    ) {}

    #[NoReturn]
    public function __invoke(ChangeRequestStatusCommand $command): void
    {
        $requestInfo = $this->repo->findById($command->requestId);
        if (!$requestInfo) {
            throw new DomainException('RequestInformation not found.');
        }

        // Buscar el status por código (puedes adaptarlo para buscar por ID si prefieres)
        $newStatus = $this->statusRepo->findByCode($command->newStatus);
        if (!$newStatus) {
            throw new DomainException('Status not found.');
        }

        $requestInfo->setStatus($newStatus);
        $requestInfo->setUpdatedAt(new \DateTimeImmutable());

        $this->repo->save($requestInfo);
    }
}
