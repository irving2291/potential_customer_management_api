<?php

namespace App\PotentialCustomer\Domain\Entity;

class Email
{
    private string $value;
    private \DateTimeImmutable $registeredAt;

    public function __construct(string $value, ?\DateTimeImmutable $registeredAt = null)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email");
        }
        $this->value = strtolower($value);
        $this->registeredAt = $registeredAt ?? new \DateTimeImmutable();
    }

    public function getValue(): string { return $this->value; }
    public function getRegisteredAt(): \DateTimeImmutable { return $this->registeredAt; }
}
