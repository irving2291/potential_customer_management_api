<?php

// src/PotentialCustomer/Infrastructure/Persistence/DoctrinePotentialCustomerEntity.php
namespace App\PotentialCustomer\Infrastructure\Persistence;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "potential_customer")]
class DoctrinePotentialCustomerEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\Column(type: "string", length: 80)]
    private string $firstName;

    #[ORM\Column(type: "string", length: 80)]
    private string $lastName;

    #[ORM\OneToMany(
        mappedBy: "potentialCustomer",
        targetEntity: DoctrineEmailEntity::class,
        cascade: ["persist", "remove"],
        orphanRemoval: true
    )]
    private Collection $emails;

    #[ORM\Column(type: "string", length: 32, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: "string", length: 64, nullable: true)]
    private ?string $city = null;

    public function __construct()
    {
        $this->emails = new ArrayCollection();
    }

    public function getId(): ?string { return $this->id; }
    public function setFirstName(string $firstName): self { $this->firstName = $firstName; return $this; }
    public function getFirstName(): string { return $this->firstName; }
    public function setLastName(string $lastName): self { $this->lastName = $lastName; return $this; }
    public function getLastName(): string { return $this->lastName; }
    public function setPhone(?string $phone): self { $this->phone = $phone; return $this; }
    public function getPhone(): ?string { return $this->phone; }
    public function setCity(?string $city): self { $this->city = $city; return $this; }
    public function getCity(): ?string { return $this->city; }

    /** @return Collection|DoctrineEmailEntity[] */
    public function getEmails(): Collection { return $this->emails; }

    public function addEmail(DoctrineEmailEntity $email): self
    {
        if (!$this->emails->contains($email)) {
            $this->emails[] = $email;
            $email->setPotentialCustomer($this);
        }
        return $this;
    }
}
