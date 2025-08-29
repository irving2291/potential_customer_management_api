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
use App\RequestInformation\Domain\Events\RequestInformationCreated;
use App\RequestInformation\Domain\Service\AssignmentService;
use App\Common\Infrastructure\EventPublisher;

use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class CreateRequestInformationHandler
{
    public function __construct(
        private PotentialCustomerRepositoryInterface      $customerRepo,
        private RequestInformationRepositoryInterface     $requestInfoRepo,
        private RequestInformationStatusRepositoryInterface $statusRepo,
        private EventPublisher                             $eventsPublisher,
        private AssignmentService                          $assignmentService,
    ) {}

    #[NoReturn]
    public function __invoke(CreateRequestInformationCommand $command): void
    {
        try {
            // 1) Buscar/crear PotentialCustomer
            $potentialCustomer = $this->customerRepo->findOneByEmail($command->email);

            if (!$potentialCustomer) {
                $potentialCustomer = new PotentialCustomer(
                    uuid_create(UUID_TYPE_RANDOM),
                    $command->type,
                    $command->organization,
                    [new EmailEntity($command->email)],
                    $command->phone,
                    $command->priority,
                    $command->firstName,
                    $command->lastName,
                    null,
                    $command->city,
                    null,
                    null,
                );
                $this->customerRepo->save($potentialCustomer);
            } elseif (!$potentialCustomer->hasEmail($command->email)) {
                $potentialCustomer->addEmail(new EmailEntity($command->email));
                $this->customerRepo->save($potentialCustomer);
            }

            // 2) Regla de idempotencia funcional (3 meses)
            $exist = $this->requestInfoRepo->existsByEmailProgramAndLeadInThreeMonth(
                $command->email,
                $command->programInterest,
                $command->leadOrigin
            );
            if ($exist) {
                throw new \DomainException('Ya existe una petición para este programa, lead y persona.');
            }

            // 3) Estado por defecto
            $defaultStatus = $this->statusRepo->findDefault();
            if (!$defaultStatus) {
                throw new \DomainException('No existe un estado por defecto configurado.');
            }

            // 4) Crear RequestInformation (Aggregate)
            $request = new RequestInformation(
                uuid_create(UUID_TYPE_RANDOM),
                $command->programInterest,
                $command->leadOrigin,
                $command->organization, // <- UUID de la organización (tenant)
                $defaultStatus,
                $command->firstName,
                $command->lastName,
                new EmailValueObject($command->email),
                new Phone($command->phone),
                $command->city
            );

            // 5) Asignar responsable según reglas de asignación
            try {
                $assigneeId = $this->assignmentService->assignResponsible($request);
                if ($assigneeId) {
                    $request->assignTo($assigneeId);
                }
            } catch (\Exception $e) {
                // Log the assignment error but don't fail the request creation
                error_log('Assignment failed: ' . $e->getMessage());
            }

            // 6) Persistir RequestInformation
            $this->requestInfoRepo->save($request);

            // 7) Publicar evento de dominio hacia /events (DynamoDB)
            //    Si tu Command trae actor, úsalo; si no, publica como "system".
            $actorId = \property_exists($command, 'actorId') && $command->actorId ? (string)$command->actorId : 'system';
            $actorUsername = \property_exists($command, 'actorUsername') && $command->actorUsername ? (string)$command->actorUsername : 'system';

            $event = new RequestInformationCreated(
                organizationId: (string)$command->organization,               // tenantId
                requestId:      (string)($request->getId() ?? $request->id()), // depende de tus getters
                actorId:        $actorId,
                actorUsername:  $actorUsername,
                payload: [
                    'programInterest' => $command->programInterest,
                    'leadOrigin'      => $command->leadOrigin,
                    'email'           => $command->email,
                    'firstName'       => $command->firstName,
                    'lastName'        => $command->lastName,
                    'city'            => $command->city,
                    'assigneeId'      => $request->getAssigneeId(),
                ],
            );

            // Publica a través del puerto (tu adaptador hará POST /events con X-Tenant-Id y X-Trace-Id)
            // $this->eventsPublisher->publish($event);

        } catch (\DomainException $exception) {
            // No reintentar: error de negocio
            throw new UnrecoverableMessageHandlingException($exception->getMessage(), 0, $exception);
        } catch (\Throwable $e) {
            // Si quieres que un fallo de publicación NO bloquee la creación, cambia a log y return.
            // Por ahora, lo marcamos no recuperable para que lo veas inmediatamente.
            throw new UnrecoverableMessageHandlingException('Error publicando evento: '.$e->getMessage(), 0, $e);
        }
    }
}
