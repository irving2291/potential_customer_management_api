<?php

namespace App\Tests\RequestInformation\Domain\Aggregate;

use App\RequestInformation\Domain\Aggregate\RequestInformation;
use App\RequestInformation\Domain\Entity\RequestInformationStatus;
use App\RequestInformation\Domain\ValueObject\Email;
use App\RequestInformation\Domain\ValueObject\Phone;
use PHPUnit\Framework\TestCase;

class RequestInformationTest extends TestCase
{
    public function testCreateRequestInformation(): void
    {
        $status = new RequestInformationStatus('test-id', 'test-code', 'test-name', true, 'a851bb2c-6748-4f4f-a3f9-243889b2d834');
        $info = new RequestInformation(
            null,
            'product-uuid',
            'lead-uuid',
            'a851bb2c-6748-4f4f-a3f9-243889b2d834',
            $status,
            'Irving',
            'Jacome',
            new Email('test@correo.com'),
            new Phone('+593991112233'),
            'Quito'
        );

        $this->assertEquals('Irving', $info->getFirstName());
        $this->assertInstanceOf(RequestInformationStatus::class, $info->getStatus());
        $this->assertEquals($status, $info->getStatus());
    }
}

