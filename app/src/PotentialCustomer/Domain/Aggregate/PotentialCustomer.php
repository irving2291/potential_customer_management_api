<?php

namespace App\PotentialCustomer\Domain\Aggregate;

use App\PotentialCustomer\Domain\Entity\Email;

class PotentialCustomer
{
    private string $id;
    private string $firstName;
    private string $lastName;
    /** @var Email[] */
    private array $emails = [];
    private ?string $phone = null;
    private ?string $city = null;

    public function __construct(
        string $id,
        string $firstName,
        string $lastName,
        array $emails,
        ?string $phone = null,
        ?string $city = null
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->emails = $emails;
        $this->phone = $phone;
        $this->city = $city;
    }

    // ---- GETTERS ----

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return Email[]
     */
    public function getEmails(): array
    {
        return $this->emails;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    // ---- SETTERS ----

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @param Email[] $emails
     */
    public function setEmails(array $emails): void
    {
        $this->emails = $emails;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    // ---- OTHER LOGIC ----

    public function addEmail(Email $email): void
    {
        foreach ($this->emails as $existing) {
            if ($existing->getValue() === $email->getValue()) {
                return; // Ya existe, no la agregues de nuevo
            }
        }
        $this->emails[] = $email;
    }

    public function hasEmail(string $email): bool
    {
        foreach ($this->emails as $existing) {
            if ($existing->getValue() === strtolower($email)) {
                return true;
            }
        }
        return false;
    }
}
