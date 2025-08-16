<?php

namespace App\RequestInformation\Infrastructure\Persistence;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "request_information_status")]
#[ORM\UniqueConstraint(name: "uniq_code_organization", columns: ["code", "organization_id"])]
class DoctrineRequestInformationStatusEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(type: "string", length: 50, unique: true)]
    private string $code;

    #[ORM\Column(type: "string", length: 100)]
    private string $name;

    #[ORM\Column(type: "string", length: 36)]
    private string $organizationId;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $sort = 0;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $isDefault = false;

    public function __construct(
        string $code,
        string $name,
        ?bool $isDefault,
        string $organizationId
    )
    {
        $this->code = $code;
        $this->name = $name;
        $this->organizationId = $organizationId;
        $this->isDefault = $isDefault ?? false;
    }

    public function getId(): string { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getOrganization(): string { return $this->organizationId; }
    public function isDefault(): bool { return $this->isDefault; }

    public function getSort(): int { return $this->sort; }

    public function setCode(string $code): self { $this->code = $code; return $this; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setOrganizationId(string $organizationId): self { $this->organizationId = $organizationId; return $this; }
    public function setIsDefault(bool $isDefault): self { $this->isDefault = $isDefault; return $this; }
    public function setSort(int $sort): self { $this->sort = $sort; return $this; }
}
