<?php

// src/PotentialCustomer/Infrastructure/Persistence/DoctrineEmailEntity.php
namespace App\PotentialCustomer\Infrastructure\Persistence;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "potential_customer_email")]
class DoctrineEmailEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $value;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $registeredAt;

    #[ORM\ManyToOne(targetEntity: DoctrinePotentialCustomerEntity::class, inversedBy: "emails")]
    #[ORM\JoinColumn(nullable: false)]
    private ?DoctrinePotentialCustomerEntity $potentialCustomer = null;

    public function getId(): ?string { return $this->id; }
    public function getValue(): string { return $this->value; }
    public function setValue(string $value): self { $this->value = strtolower($value); return $this; }
    public function getRegisteredAt(): \DateTimeImmutable { return $this->registeredAt; }
    public function setRegisteredAt(\DateTimeImmutable $registeredAt): self { $this->registeredAt = $registeredAt; return $this; }
    public function getPotentialCustomer(): ?DoctrinePotentialCustomerEntity { return $this->potentialCustomer; }
    public function setPotentialCustomer(?DoctrinePotentialCustomerEntity $pc): self { $this->potentialCustomer = $pc; return $this; }
}
