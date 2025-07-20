<?php

namespace App\RequestInformation\Domain\ValueObject;

class Email
{
    private string $value;

    public function __construct(string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address");
        }
        $this->value = $value;
    }

    public function getValue(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
