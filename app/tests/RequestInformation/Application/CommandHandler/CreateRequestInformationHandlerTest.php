<?php
namespace App\Tests\RequestInformation\Application\CommandHandler;

use App\RequestInformation\Domain\Entity\RequestInformationStatus;
use App\RequestInformation\Domain\Repository\RequestInformationStatusRepositoryInterface;
use JetBrains\PhpStorm\NoReturn;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use App\RequestInformation\Application\CommandHandler\CreateRequestInformationHandler;
use App\RequestInformation\Application\Command\CreateRequestInformationCommand;
use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use App\PotentialCustomer\Domain\Repository\PotentialCustomerRepositoryInterface;
use App\RequestInformation\Domain\ValueObject\Email as EmailVO;
use App\RequestInformation\Domain\ValueObject\Phone;
use App\PotentialCustomer\Domain\Aggregate\PotentialCustomer;

class CreateRequestInformationHandlerTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[NoReturn] public function testCreateRequestInformationSuccess(): void
    {
        $mockCustomerRepo = $this->createMock(PotentialCustomerRepositoryInterface::class);
        $mockRequestRepo = $this->createMock(RequestInformationRepositoryInterface::class);
        $mockStatusRepo = $this->createMock(RequestInformationStatusRepositoryInterface::class);

        // El potencial cliente no existe aún
        $mockCustomerRepo->expects($this->once())
            ->method('findOneByEmail')
            ->with('irving@correo.com')
            ->willReturn(null);

        // El request information tampoco debe existir con esa combinación
        $mockRequestRepo->expects($this->once())
            ->method('existsByEmailProgramAndLeadInThreeMonth')
            ->with('irving@correo.com', 'product-uuid', 'lead-uuid')
            ->willReturn(false);

        $mockStatusRepo->expects($this->once())
            ->method('findDefault')
            ->willReturn(new RequestInformationStatus(
                'test-id', 'test-code', 'test-name', true, 'a851bb2c-6748-4f4f-a3f9-243889b2d834'
            ));

        // Esperamos que save se llame en ambos repos
        $mockCustomerRepo->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(PotentialCustomer::class));
        $mockRequestRepo->expects($this->once())
            ->method('save')
            ->with($this->callback(function($ri) {
                return $ri->getEmail() instanceof EmailVO
                    && $ri->getPhone() instanceof Phone;
            }));

        $handler = new CreateRequestInformationHandler($mockCustomerRepo, $mockRequestRepo, $mockStatusRepo);

        $command = new CreateRequestInformationCommand(
            'product-uuid',
            'lead-uuid',
            'a851bb2c-6748-4f4f-a3f9-243889b2d834',
            'Irving',
            'Jacome',
            'irving@correo.com',
            '0999123456',
            'Quito'
        );

        $handler->__invoke($command);
        $this->assertTrue(true); // Llegó al final sin excepción
    }

    /**
     * @throws Exception
     */
    #[NoReturn] public function testThrowsOnDuplicateRequest(): void
    {
        $mockCustomerRepo = $this->createMock(PotentialCustomerRepositoryInterface::class);
        $mockRequestRepo = $this->createMock(RequestInformationRepositoryInterface::class);
        $mockStatusRepo = $this->createMock(RequestInformationStatusRepositoryInterface::class);

        $mockCustomerRepo->method('findOneByEmail')->willReturn(null);
        $mockRequestRepo->method('existsByEmailProgramAndLeadInThreeMonth')->willReturn(true);
        $mockStatusRepo->method('findDefault')->willReturn(
            new RequestInformationStatus('test-id', 'test-code', 'test-name', true, 'a851bb2c-6748-4f4f-a3f9-243889b2d834')
        );

        $handler = new CreateRequestInformationHandler($mockCustomerRepo, $mockRequestRepo, $mockStatusRepo);

        $command = new CreateRequestInformationCommand(
            'product-uuid',
            'lead-uuid',
            'a851bb2c-6748-4f4f-a3f9-243889b2d834',
            'Irving',
            'Jacome',
            'irving@correo.com',
            '0999123456',
            'Quito'
        );

        try {
            $handler->__invoke($command);
            $this->fail('Expected exception not thrown');
        } catch (\Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException $ex) {
            // Puedes también verificar que el mensaje es el esperado:
            $this->assertInstanceOf(\DomainException::class, $ex->getPrevious());
            $this->assertEquals('Ya existe una petición para este programa, lead y persona.', $ex->getPrevious()->getMessage());
        } catch (\DomainException $ex) {
            // Si NO usas Messenger, caerá aquí directamente
            $this->assertEquals('Ya existe una petición para este programa, lead y persona.', $ex->getMessage());
        }
    }
}
