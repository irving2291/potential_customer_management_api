<?php

namespace App\RequestInformation\Application\Command;

final class UpdateRequestInformationStatusCommand
{
    public function __construct(
        private readonly string $id,
        private readonly string $organizationId,
        private readonly ?string $code = null,
        private readonly ?string $name = null,
        private readonly ?int $sort = null
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }
}
