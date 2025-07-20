<?php
namespace App\RequestInformation\Application\Command;

class CreateRequestInformationCommand
{
    public string $programInterest;
    public string $leadOrigin;
    public string $firstName;
    public string $lastName;
    public string $email;
    public string $phone;
    public string $city;

    public function __construct(
        string $programInterest,
        string $leadOrigin,
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $city,
    )
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->city = $city;
        $this->programInterest = $programInterest;
        $this->leadOrigin = $leadOrigin;
    }
}
