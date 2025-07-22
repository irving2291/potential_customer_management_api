<?php

namespace App\RequestInformation\Application\CommandHandler;

use App\RequestInformation\Application\Command\UpdateRequestNoteCommand;
use App\RequestInformation\Domain\Repository\RequestNoteRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateRequestNoteHandler
{
    public function __construct(private RequestNoteRepositoryInterface $repository) {}

    public function __invoke(UpdateRequestNoteCommand $command): void
    {
        $note = $this->repository->findById($command->noteId);
        $note->text = $command->text;
        $note->updatedAt = new \DateTimeImmutable();
        $this->repository->save($note);
    }
}

