<?php

namespace App\RequestInformation\Domain\Events;

use App\Common\Domain\DomainEvent;

class RequestStatusChanged implements DomainEvent
{
    public function __construct(
        private string $organizationId,   // tenant
        private string $requestId,        // uuid de la entidad
        private string $actorId,
        private string $actorUsername,
        private array $payload = [],
        private ?\DateTimeImmutable $when = null,
    ) {}

    public function eventType(): string { return 'request_information_status.created'; }

    public function occurredOn(): \DateTimeImmutable { return $this->when ?? new \DateTimeImmutable(); }

    public function payload(): array { return $this->payload; }

    public function tenantId(): ?string { return $this->organizationId; }

    public function entityId(): string { return 'request_information_'.$this->requestId; }

    public function actorId(): ?string { return $this->actorId; }

    public function actorUsername(): ?string { return $this->actorUsername; }
}
