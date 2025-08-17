<?php

namespace App\RequestInformation\Domain\Entity;

class RequestInformationStatus
{
    public function __construct(
        private readonly string $id,
        private string          $code,
        private string          $name,
        private string          $isDefault,
        public string           $organizationId,
        private string           $sort,
    ) {}

    public function getId(): string { return $this->id; }

    public function getCode(): string { return $this->code; }

    public function getName(): string { return $this->name; }

    public function getIsDefault(): string { return $this->isDefault; }

    public function getOrganizationId(): string { return $this->organizationId; }

    public function getSort(): string { return $this->sort; }

    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function setName(string $name): self { $this->name = $name; return $this; }

    public function setIsDefault(string $isDefault): self { $this->isDefault = $isDefault; return $this; }

    public function setOrganizationId(string $organizationId): self { $this->organizationId = $organizationId; return $this; }

    public function setSort(string $sort): self { $this->sort = $sort; return $this; }
}
