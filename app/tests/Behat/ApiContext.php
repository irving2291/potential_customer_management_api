<?php

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class ApiContext implements Context
{
    private ?Response $response = null;
    private ?KernelBrowser $client = null;

    public function __construct(KernelInterface $kernel)
    {
        $this->client = new KernelBrowser($kernel);
    }

    /**
     * @When I send a GET request to :path
     */
    public function iSendAGetRequestTo($path): void
    {
        $this->client->request('GET', $path);
        $this->response = $this->client->getResponse();
    }

    /**
     * @Then the response status code should be :statusCode
     */
    public function theResponseStatusCodeShouldBe($statusCode): void
    {
        if ((string) $this->response->getStatusCode() !== (string) $statusCode) {
            throw new \Exception("Expected status code $statusCode, got ".$this->response->getStatusCode());
        }
    }

    /**
     * @Then the response should be in JSON
     */
    public function theResponseShouldBeInJson(): void
    {
        json_decode($this->response->getContent());
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Response is not valid JSON: ' . $this->response->getContent());
        }
    }

    /**
     * @Then the JSON should contain the field :field
     */
    public function theJsonShouldContainTheField($field): void
    {
        $data = json_decode($this->response->getContent(), true);
        if (!array_key_exists($field, $data)) {
            throw new \Exception("Field '$field' not found in JSON response. Full response: " . json_encode($data));
        }
    }
}
