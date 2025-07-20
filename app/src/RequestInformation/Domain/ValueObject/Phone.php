<?php

namespace App\RequestInformation\Domain\ValueObject;

class Phone
{
    private string $value;

    public function __construct(string $value)
    {
        // Puedes validar formato según tus reglas
        if (strlen($value) < 8) {
            throw new \InvalidArgumentException("Invalid phone number");
        }
        $this->value = $value;
    }

    public function getValue(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
