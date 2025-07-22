<?php

namespace App\RequestInformation\Application\CommandHandler;

use App\RequestInformation\Application\Command\DeleteRequestNoteCommand;
use App\RequestInformation\Domain\Repository\RequestNoteRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteRequestNoteHandler
{
    public function __construct(private RequestNoteRepositoryInterface $repo) {}

    public function __invoke(DeleteRequestNoteCommand $command): void
    {
        $note = $this->repo->findById($command->noteId);
        $note->deletedAt = new \DateTimeImmutable();
        $this->repo->save($note);
    }
}

