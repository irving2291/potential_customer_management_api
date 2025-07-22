<?php

namespace App\RequestInformation\Application\Command;

class ChangeRequestStatusCommand
{
    public function __construct(
        public readonly string $requestId,
        public readonly string $newStatus,
        public readonly ?string $userId = null // Opcional: usuario que hizo el cambio
    ) {}
}
