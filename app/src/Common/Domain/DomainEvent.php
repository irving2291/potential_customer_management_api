<?php
declare(strict_types=1);

namespace App\Common\Domain;

interface DomainEvent
{
    public function eventType(): string;                 // p.ej. "request_information.created"
    public function occurredOn(): \DateTimeImmutable;    // fecha/hora del evento
    public function payload(): array;                    // datos (sin PII sensible)
    public function tenantId(): ?string;                 // UUID de la organización
    public function entityId(): string;                  // "request_information_{uuid}"
    public function actorId(): ?string;                  // UUID del usuario
    public function actorUsername(): ?string;            // username del usuario
}
