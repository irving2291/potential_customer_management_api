<?php

namespace App\RequestInformation\Application\Command;

class DeleteRequestNoteCommand
{
    public function __construct(public readonly string $noteId) {}
}
