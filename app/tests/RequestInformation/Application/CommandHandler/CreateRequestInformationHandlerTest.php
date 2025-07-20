<?php

use PHPUnit\Framework\TestCase;
use App\RequestInformation\Application\CommandHandler\CreateRequestInformationHandler;
use App\RequestInformation\Application\Command\CreateRequestInformationCommand;
use App\PotentialCustomer\Domain\Repository\PotentialCustomerRepositoryInterface;
use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use App\PotentialCustomer\Domain\Aggregate\PotentialCustomer;

class InMemoryPotentialCustomerRepository implements PotentialCustomerRepositoryInterface {
    public array $customers = [];
    public function findOneByEmail(string $email): ?PotentialCustomer
    {
        return $this->customers[$email] ?? null;
    }
    public function save($request): void { $this->customers[] = $request; }
}

class InMemoryRequestInformationRepository implements RequestInformationRepositoryInterface {
    public $requests = [];
    public function save($request): void { $this->requests[] = $request; }

    public function countAll(): int
    {
        return count($this->requests);
    }

    public function existsByEmailProgramAndLeadInThreeMonth(string $email, string $programId, string $leadId): bool
    {
        return isset($this->requests[$email][$programId][$leadId]);
    }
}

class CreateRequestInformationHandlerTest extends TestCase
{
    public function test_handler_creates_new_potential_customer_if_not_exists()
    {
        $customerRepo = new InMemoryPotentialCustomerRepository();
        $requestRepo = new InMemoryRequestInformationRepository();
        $handler = new CreateRequestInformationHandler($customerRepo, $requestRepo);

        $command = new CreateRequestInformationCommand(
            'prod-uid',
            'lead-uid',
            'Irving',
            'Jacome',
            'test@email.com',
            '1234567',
            'Quito'
        );

        $handler->__invoke($command);

        $this->assertCount(1, $customerRepo->customers);
        $this->assertCount(1, $requestRepo->requests);
    }
}
