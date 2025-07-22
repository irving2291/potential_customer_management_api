<?php

namespace App\RequestInformation\Application\CommandHandler;

use App\RequestInformation\Application\Command\AddRequestNoteCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\RequestInformation\Domain\Entity\RequestNote;
use App\RequestInformation\Domain\Repository\RequestNoteRepositoryInterface;

#[AsMessageHandler]
class AddRequestNoteHandler
{
    public function __construct(private RequestNoteRepositoryInterface $noteRepo) {}

    public function __invoke(AddRequestNoteCommand $command): void
    {
        $note = new RequestNote(
            uuid_create(UUID_TYPE_RANDOM),
            $command->requestInformationId,
            $command->text,
            $command->createdBy,
            new \DateTimeImmutable()
        );
        $this->noteRepo->save($note);
    }
}
