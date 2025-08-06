<?php

namespace App\RequestInformation\Application\CommandHandler;

use App\RequestInformation\Application\Command\AddRequestInformationStatusCommand;
use App\RequestInformation\Domain\Entity\RequestInformationStatus;
use App\RequestInformation\Domain\Repository\RequestInformationStatusRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AddRequestInformationStatusHandler
{
    public function __construct(private RequestInformationStatusRepositoryInterface $noteRepo) {}

    public function __invoke(AddRequestInformationStatusCommand $command): void
    {
        $note = new RequestInformationStatus(
            uuid_create(UUID_TYPE_RANDOM),
            $command->code,
            $command->name,
            false,
            $command->organizationId
        );
        $this->noteRepo->save($note);
    }
}
