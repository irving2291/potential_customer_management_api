<?php

namespace App\RequestInformation\Application\Command;

class AddRequestInformationStatusCommand
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $organizationId
    ) {}
}
