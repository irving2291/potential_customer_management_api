<?php

namespace App\RequestInformation\Application\Command;

class AddRequestNoteCommand
{
    public function __construct(
        public readonly string $requestInformationId,
        public readonly string $text,
        public readonly string $createdBy
    ) {}
}
