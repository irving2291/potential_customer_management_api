<?php
namespace App\RequestInformation\Application\CommandHandler;

use App\PotentialCustomer\Domain\Aggregate\PotentialCustomer;
use App\PotentialCustomer\Domain\Entity\Email as EmailEntity;
use App\PotentialCustomer\Domain\Repository\PotentialCustomerRepositoryInterface;
use App\RequestInformation\Domain\Aggregate\RequestInformation;
use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use App\RequestInformation\Application\Command\CreateRequestInformationCommand;
use App\RequestInformation\Domain\Repository\RequestInformationStatusRepositoryInterface;
use App\RequestInformation\Domain\ValueObject\Email as EmailValueObject;
use App\RequestInformation\Domain\ValueObject\Phone;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class CreateRequestInformationHandler
{
    public function __construct(
        private PotentialCustomerRepositoryInterface  $customerRepo,
        private RequestInformationRepositoryInterface $requestInfoRepo,
        private RequestInformationStatusRepositoryInterface $statusRepo
    ) {}

    #[NoReturn] public function __invoke(CreateRequestInformationCommand $command): void
    {
        try {

            $potentialCustomer = $this->customerRepo->findOneByEmail($command->email);

            if (!$potentialCustomer) {
                $potentialCustomer = new PotentialCustomer(
                    uuid_create(UUID_TYPE_RANDOM), // o usa tu generador de UUID
                    $command->firstName,
                    $command->lastName,
                    [new EmailEntity($command->email)],
                    $command->phone,
                    $command->city
                );
                $this->customerRepo->save($potentialCustomer);
            } elseif (!$potentialCustomer->hasEmail($command->email)) {
                // Exist, but it's a new email
                $potentialCustomer->addEmail(new EmailEntity($command->email));
                $this->customerRepo->save($potentialCustomer);
            }
            $exist = $this->requestInfoRepo->existsByEmailProgramAndLeadInThreeMonth(
                $command->email,
                $command->programInterest,
                $command->leadOrigin
            );
            if ($exist) {
                throw new \DomainException('Ya existe una petición para este programa, lead y persona.');
            }

            $defaultStatus = $this->statusRepo->findDefault();
            if (!$defaultStatus) {
                throw new \DomainException('No existe un estado por defecto configurado.');
            }

            $request = new RequestInformation(
                uuid_create(UUID_TYPE_RANDOM),
                $command->programInterest,
                $command->leadOrigin,
                $command->organization,
                $defaultStatus,
                $command->firstName,
                $command->lastName,
                new EmailValueObject($command->email),
                new Phone($command->phone),
                $command->city
            );
            $this->requestInfoRepo->save($request);
        } catch (\DomainException $exception) {
            throw new UnrecoverableMessageHandlingException($exception->getMessage(), 0, $exception);
        }
    }
}
