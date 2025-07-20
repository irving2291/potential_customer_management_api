<?php

namespace App\RequestInformation\Domain\Aggregate;

use App\RequestInformation\Domain\ValueObject\Email;
use App\RequestInformation\Domain\ValueObject\Phone;
use App\RequestInformation\Domain\ValueObject\RequestStatus;

class RequestInformation
{
    private ?string $id;
    private string $programInterestId; // Product UID
    private string $leadOriginId;      // LeadOrigin UID
    private RequestStatus $status;
    private string $firstName;
    private string $lastName;
    private Email $email;
    private Phone $phone;
    private string $city;

    public function __construct(
        ?string $id,
        string $programInterestId,
        string $leadOriginId,
        ?RequestStatus $status,
        string $firstName,
        string $lastName,
        Email $email,
        Phone $phone,
        string $city
    ) {
        $this->id = $id;
        $this->programInterestId = $programInterestId;
        $this->leadOriginId = $leadOriginId;
        $this->status = $status ?? RequestStatus::NEW;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->city = $city;
    }

    // --- GETTERS ---
    public function getStatus(): RequestStatus { return $this->status; }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProgramInterestId(): string
    {
        return $this->programInterestId;
    }

    public function getLeadOriginId(): string
    {
        return $this->leadOriginId;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPhone(): Phone
    {
        return $this->phone;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    // --- SETTERS (opcionales, usa con precaución en DDD) ---

    public function setStatus(RequestStatus $status): void { $this->status = $status; }

    public function setProgramInterestId(string $programInterestId): self
    {
        $this->programInterestId = $programInterestId;
        return $this;
    }

    public function setLeadOriginId(string $leadOriginId): self
    {
        $this->leadOriginId = $leadOriginId;
        return $this;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setEmail(Email $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setPhone(Phone $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }
}
