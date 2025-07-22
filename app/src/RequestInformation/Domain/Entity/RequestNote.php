<?php

namespace App\RequestInformation\Domain\Entity;

class RequestNote
{
    public function __construct(
        public readonly string $id,
        public readonly string $requestInformationId,
        public string $text,
        public readonly string $createdBy,
        public readonly \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt = null,
        public ?\DateTimeImmutable $deletedAt = null
    ) {}
}
