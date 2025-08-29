<?php
namespace App\RequestInformation\Application\Command;

class CreateRequestInformationCommand
{
    public string $type;
    public string $programInterest;
    public string $leadOrigin;
    public string $organization;
    public string $firstName;
    public string $lastName;
    public string $email;
    public string $phone;
    public string $city;

    public function __construct(
        string $programInterest,
        string $leadOrigin,
        string $organization,
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $city,
        string $type = 'person',
        string $priority = 'medium',
    )
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->city = $city;
        $this->programInterest = $programInterest;
        $this->leadOrigin = $leadOrigin;
        $this->organization = $organization;
        $this->type = $type;
        $this->priority = $priority;
    }
}
