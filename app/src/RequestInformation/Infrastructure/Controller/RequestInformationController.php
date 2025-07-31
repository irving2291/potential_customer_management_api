<?php
namespace App\RequestInformation\Infrastructure\Controller;

use App\RequestInformation\Application\Command\AddRequestNoteCommand;
use App\RequestInformation\Application\Command\ChangeRequestStatusCommand;
use App\RequestInformation\Application\Command\DeleteRequestNoteCommand;
use App\RequestInformation\Application\Command\UpdateRequestNoteCommand;
use App\RequestInformation\Application\CommandHandler\AddRequestNoteHandler;
use App\RequestInformation\Application\CommandHandler\ChangeRequestStatusHandler;
use App\RequestInformation\Application\CommandHandler\DeleteRequestNoteHandler;
use App\RequestInformation\Application\Query\GetRequestSummaryQuery;
use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use App\RequestInformation\Domain\Repository\RequestNoteRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use App\RequestInformation\Application\Command\CreateRequestInformationCommand;
use OpenApi\Attributes as OA;

class RequestInformationController extends AbstractController
{
    /**
     * @throws ExceptionInterface
     */
    #[Route('/api/v1/requests-information', name: 'create_request_information', methods: ['POST'])]
    #[OA\Post(
        summary: "Crear una petición de información",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["programInterest", "leadOrigin", "firstName", "lastName", "email", "phone", "city"],
                properties: [
                    new OA\Property(property: "programInterest", type: "string"),
                    new OA\Property(property: "leadOrigin", type: "string"),
                    new OA\Property(property: "firstName", type: "string"),
                    new OA\Property(property: "lastName", type: "string"),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "phone", type: "string"),
                    new OA\Property(property: "city", type: "string")
                ]
            )
        ),
        tags: ['RequestInformation'],
        responses: [
            new OA\Response(
                response: 200,
                description: "Petición creada correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "ok")
                    ]
                )
            )
        ]
    )]
    public function create(
        Request $request,
        MessageBusInterface $commandBus
    ): JsonResponse {
        $organizationId = $request->headers->get('organization');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'No data organization', 'debug_content' => $request->headers->get('organization')], 400);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => true, 'message' => 'No data received', 'debug_content' => $request->getContent()], 400);
        }
        if (!isset($data['programInterest'])) {
            return $this->json(['error' => true, 'message' => 'Missing field: programInterest', 'received' => $data], 400);
        }

        $command = new CreateRequestInformationCommand(
            $data['programInterest'],
            $data['leadOrigin'],
            $organizationId,
            $data['firstName'],
            $data['lastName'],
            $data['email'],
            $data['phone'],
            $data['city']
        );
        $commandBus->dispatch($command);

        return $this->json(['status' => 'ok']);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/api/v1/requests-information/summary', name: 'requests_information_summary', methods: ['GET'])]
    #[OA\Get(
        summary: "Summary of requests by state in a date range",
        tags: ['RequestInformation'],
        parameters: [
            new OA\Parameter(
                name: "from",
                description: "Start date (YYYY-MM-DD)",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", format: "date")
            ),
            new OA\Parameter(
                name: "to",
                description: "End date (YYYY-MM-DD)",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", format: "date")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Total and status breakdown",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "total", type: "integer"),
                        new OA\Property(property: "byStatus", type: "object", additionalProperties: new OA\AdditionalProperties(type: "integer"))
                    ]
                )
            )
        ]
    )]
    public function summary(
        Request $request,
        MessageBusInterface $queryBus
    ): JsonResponse {
        $from = $request->query->get('from');
        $to = $request->query->get('to');

        $fromDate = $from ? \DateTimeImmutable::createFromFormat('Y-m-d', $from) : null;
        $toDate = $to ? \DateTimeImmutable::createFromFormat('Y-m-d', $to) : null;

        $query = new GetRequestSummaryQuery($fromDate, $toDate);
        $envelope = $queryBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);

        return $this->json($handledStamp->getResult());
    }



    #[Route('/api/v1/requests-information', name: 'requests_information_list', methods: ['GET'])]
    #[OA\Get(
        summary: "Lista peticiones por estado, paginadas",
        tags: ['RequestInformation'],
        parameters: [
            new OA\Parameter(
                name: "status",
                description: "Estado a consultar (NEW, IN_PROGRESS, RECONTACT, WON, LOST)",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "limit",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 10)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Listado paginado por estado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "string"),
                                new OA\Property(property: "fistName", type: "string"),
                                new OA\Property(property: "lastName", type: "string"),
                                new OA\Property(property: "email", type: "string"),
                                new OA\Property(property: "phone", type: "string"),
                                new OA\Property(property: "createdAt", type: "string"),
                                new OA\Property(property: "updatedAt", type: "string"),
                                new OA\Property(property: "status", type: "string"),
                            ]
                        )),
                        new OA\Property(property: "page", type: "integer"),
                        new OA\Property(property: "limit", type: "integer"),
                        new OA\Property(property: "count", type: "integer")
                    ]
                )
            )
        ]
    )]
    public function list(
        Request $request,
        RequestInformationRepositoryInterface $repo
    ): JsonResponse {
        $status = $request->query->get('status');
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 10);

        if (!$status) {
            return $this->json([
                'error' => true,
                'message' => 'El parámetro "status" es requerido.'
            ], 400);
        }

        $result = $repo->findByStatusPaginated($status, $page, $limit);

        // Mapear los datos a un array limpio para API
        $data = array_map(function($item) {
            return [
                'id' => $item->getId(),
                'fistName' => $item->getFirstName(),
                'lastName' => $item->getLastName(),
                'email' => $item->getEmail(),
                'phone' => $item->getPhone(),
                'createdAt' => $item->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $item->getUpdatedAt()?->format('Y-m-d H:i:s'),
                'status' => $item->getStatus(),
                // Puedes agregar más campos si lo necesitas
            ];
        }, $result);

        return $this->json([
            'data' => $data,
            'page' => $page,
            'limit' => $limit,
            'count' => count($data)
        ]);
    }


    #[Route('/api/v1/requests-information/{id}/status', name: 'change_request_status', methods: ['PATCH'])]
    #[OA\Patch(
        summary: "Change the status of a request information",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status_code"],
                properties: [
                    new OA\Property(property: "status_code", type: "string", example: "won")
                ]
            )
        ),
        tags: ['RequestInformation'],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string")),
        ],
        responses: [ new OA\Response(response: 200, description: "Status changed") ]
    )]
    public function changeStatus(
        string $id,
        Request $request,
        ChangeRequestStatusHandler $handler
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $statusCode = $data['status_code'] ?? null;

        if (!$statusCode) {
            return $this->json(['error' => true, 'message' => 'status_code is required'], 400);
        }

        $command = new ChangeRequestStatusCommand($id, $statusCode);
        $handler->__invoke($command);

        return $this->json(['success' => true]);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/api/v1/requests-information/{id}/notes', name: 'add_request_note', methods: ['POST'])]
    #[OA\Post(
        summary: "Add a note to a request information",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["text", "createdBy"],
                properties: [
                    new OA\Property(property: "text", type: "string"),
                    new OA\Property(property: "createdBy", type: "string")
                ]
            )
        ),
        tags: ['RequestNote'],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string")),
        ],
        responses: [ new OA\Response(response: 200, description: "Note added") ]
    )]
    public function addNote(
        string $id,
        Request $request,
        AddRequestNoteHandler $handler
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (empty($data['text']) || empty($data['createdBy'])) {
            return $this->json(['error' => true, 'message' => 'Text or createdBy is required.'], 400);
        }
        $command = new AddRequestNoteCommand(
            $id,
            $data['text'],
            $data['createdBy']
        );
        $handler->__invoke($command);

        return $this->json(['success' => true]);
    }

    #[Route('/api/v1/requests-information/{id}/notes', name: 'list_request_notes', methods: ['GET'])]
    #[OA\Get(
        description: "Get all notes (not soft-deleted) for a given request information.",
        summary: "List notes for a request information",
        tags: ['RequestNote'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "The id of the RequestInformation entity",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of notes",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "notes",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string"),
                                    new OA\Property(property: "text", type: "string"),
                                    new OA\Property(property: "createdBy", type: "string"),
                                    new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                                    new OA\Property(property: "updatedAt", type: "string", format: "date-time", nullable: true)
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function listNotes(
        string $id,
        RequestNoteRepositoryInterface $repo
    ): JsonResponse {
        $notes = $repo->findByRequestInformationId($id);
        // Solo notas no borradas
        // $visible = array_filter($notes, fn($n) => $n->deletedAt === null);
        $result = array_map(fn($n) => [
            'id' => $n->id,
            'text' => $n->text,
            'createdBy' => $n->createdBy,
            'createdAt' => $n->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $n->updatedAt?->format('Y-m-d H:i:s'),
        ], $notes);
        return $this->json(['notes' => $result]);
    }

    #[Route('/api/v1/requests-information/notes/{noteId}', name: 'update_request_note', methods: ['PATCH'])]
    #[OA\Patch(
        description: "Update the text and set updatedAt for a note.",
        summary: "Update the content of a note",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["text"],
                properties: [
                    new OA\Property(property: "text", type: "string", example: "This is the updated note.")
                ]
            )
        ),
        tags: ['RequestNote'],
        parameters: [
            new OA\Parameter(
                name: "noteId",
                description: "The id of the note",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Note updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true)
                    ]
                )
            )
        ]
    )]
    public function updateNote(
        string $noteId,
        Request $request,
        MessageBusInterface $commandBus
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $command = new UpdateRequestNoteCommand(
            $noteId,
            $data['text'] ?? ''
        );
        $commandBus->dispatch($command);
        return $this->json(['success' => true]);
    }

    #[Route('/api/v1/requests-information/notes/{noteId}', name: 'delete_request_note', methods: ['DELETE'])]
    #[OA\Delete(
        description: "Mark a note as deleted (soft delete, sets deletedAt).",
        summary: "Soft delete a note",
        tags: ['RequestNote'],
        parameters: [
            new OA\Parameter(
                name: "noteId",
                description: "The id of the note",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Note deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true)
                    ]
                )
            )
        ]
    )]
    public function deleteNote(
        string $noteId,
        DeleteRequestNoteHandler $handler
    ): JsonResponse {
        $command = new DeleteRequestNoteCommand($noteId);
        $handler->__invoke($command);
        return $this->json(['success' => true]);
    }

}
