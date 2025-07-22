<?php

namespace App\RequestInformation\Application\Command;

class UpdateRequestNoteCommand
{
    public function __construct(
        public readonly string $noteId,
        public readonly string $text,
        public readonly ?string $userId = null
    ) {}
}
