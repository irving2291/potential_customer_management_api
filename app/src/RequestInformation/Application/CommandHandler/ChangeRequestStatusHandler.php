<?php

namespace App\RequestInformation\Application\CommandHandler;

use App\RequestInformation\Application\Command\ChangeRequestStatusCommand;
use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use App\RequestInformation\Domain\ValueObject\RequestStatus;
use DomainException;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ChangeRequestStatusHandler
{
    public function __construct(private RequestInformationRepositoryInterface $repo) {}

    #[NoReturn] public function __invoke(ChangeRequestStatusCommand $command): void
    {
        $requestInfo = $this->repo->findById($command->requestId);
        if (!$requestInfo) {
            throw new DomainException('RequestInformation not found.');
        }

        $newStatus = RequestStatus::from(strtoupper($command->newStatus));
        $requestInfo->setStatus($newStatus);
        $requestInfo->setUpdatedAt(new \DateTimeImmutable());

        $this->repo->save($requestInfo);
    }
}
