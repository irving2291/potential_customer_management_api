<?php

namespace App\RequestInformation\Application\CommandHandler;

use App\Common\Infrastructure\EventPublisher;
use App\RequestInformation\Application\Command\ChangeRequestStatusCommand;
use App\RequestInformation\Domain\Events\RequestStatusChanged;
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
        private RequestInformationStatusRepositoryInterface $statusRepo,
        private EventPublisher $eventsPublisher,
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

        $actorId = \property_exists($command, 'actorId') && $command->actorId ? (string)$command->actorId : 'system';
        $actorUsername = \property_exists($command, 'actorUsername') && $command->actorUsername ? (string)$command->actorUsername : 'system';

        $event = new RequestStatusChanged(
            organizationId: $requestInfo->getOrganizationId(),
            requestId:      (string)($requestInfo->getId() ?? $requestInfo->id()), // depende de tus getters
            actorId:        $actorId,
            actorUsername:  $actorUsername,
            payload: (array)$newStatus,
        );
        $this->eventsPublisher->publish($event);
    }
}
