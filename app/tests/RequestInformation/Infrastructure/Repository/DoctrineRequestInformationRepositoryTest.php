<?php

namespace App\Tests\RequestInformation\Infrastructure\Repository;

use App\RequestInformation\Domain\Entity\RequestInformationStatus;
use App\RequestInformation\Domain\Repository\RequestInformationStatusRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\RequestInformation\Domain\Aggregate\RequestInformation;
use App\RequestInformation\Domain\ValueObject\Email;
use App\RequestInformation\Domain\ValueObject\Phone;
use App\RequestInformation\Domain\ValueObject\RequestStatus;
use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;

class DoctrineRequestInformationRepositoryTest extends KernelTestCase
{
    private RequestInformationRepositoryInterface $repository;
    private RequestInformationStatusRepositoryInterface $statusRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = static::getContainer()->get(RequestInformationRepositoryInterface::class);
        $this->statusRepository = static::getContainer()->get(RequestInformationStatusRepositoryInterface::class);
    }

    public function testSaveAndFindById(): void
    {
        $status = $this->statusRepository->findDefault();
        $this->assertNotNull($status);

        $info = new RequestInformation(
            uuid_create(UUID_TYPE_RANDOM),
            '38f26958-0da7-4779-899d-7c78a9fad30c',
            '5e80fc27-8c9c-4a60-a6fb-3ec119c96252',
            'a851bb2c-6748-4f4f-a3f9-243889b2d834',
            $status,
            'Irving',
            'Jacome',
            new Email('irving@correo.com'),
            new Phone('0999988777'),
            'Guayaquil'
        );
        $domain = $this->repository->save($info);

        $found = $this->repository->findById($domain->getId());

        $this->assertNotNull($found);
        $this->assertEquals('Irving', $found->getFirstName());
        $this->assertEquals('Guayaquil', $found->getCity());
    }
}

