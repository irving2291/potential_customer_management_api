<?php

namespace App\Tests\RequestInformation\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RequestInformationControllerTest extends WebTestCase
{
    public function testCreateRequestInformation(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/requests-information',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'X-Org-Id' => 'a851bb2c-6748-4f4f-a3f9-243889b2d834'
            ],
            json_encode([
                'programInterest' => '38f26958-0da7-4779-899d-7c78a9fad30c',
                'leadOrigin' => '5e80fc27-8c9c-4a60-a6fb-3ec119c96252',
                'firstName' => 'Irving',
                'lastName' => 'Jacome',
                'email' => 'irving@correo.com',
                'phone' => '0999988777',
                'city' => 'Guayaquil'
            ])
        );

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
        $this->assertStringContainsString('ok', $client->getResponse()->getContent());
    }

    public function testGetRequestInformationSummary(): void
    {
        $client = static::createClient();
        $client->request('GET', '/requests-information/summary?from=2025-07-01&to=2025-07-31');
        $this->assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        $data = json_decode($content, true);

        // Debug (opcional, útil en local para ver la respuesta si falla el decode)
        $this->assertNotNull($data, 'La respuesta no es JSON válido. Content: ' . $content);

        $this->assertArrayHasKey('total', $data, 'La respuesta no tiene el campo "total"');
        $this->assertArrayHasKey('new', $data, 'La respuesta no tiene el campo "new"');
        $this->assertArrayHasKey('in_progress', $data);
        $this->assertArrayHasKey('recontact', $data);
        $this->assertArrayHasKey('won', $data);
        $this->assertArrayHasKey('lost', $data);
    }

}
