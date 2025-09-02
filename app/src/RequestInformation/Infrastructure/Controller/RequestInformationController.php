<?php
namespace App\RequestInformation\Infrastructure\Controller;

use App\RequestInformation\Application\Command\AddRequestInformationStatusCommand;
use App\RequestInformation\Application\Command\AddRequestNoteCommand;
use App\RequestInformation\Application\Command\ChangeRequestStatusCommand;
use App\RequestInformation\Application\Command\DeleteRequestNoteCommand;
use App\RequestInformation\Application\Command\ReorderRequestInformationStatusesCommand;
use App\RequestInformation\Application\Command\UpdateRequestInformationStatusCommand;
use App\RequestInformation\Application\Command\UpdateRequestNoteCommand;
use App\RequestInformation\Application\CommandHandler\AddRequestInformationStatusHandler;
use App\RequestInformation\Application\CommandHandler\AddRequestNoteHandler;
use App\RequestInformation\Application\CommandHandler\ChangeRequestStatusHandler;
use App\RequestInformation\Application\CommandHandler\DeleteRequestNoteHandler;
use App\RequestInformation\Application\CommandHandler\ReorderRequestInformationStatusesHandler;
use App\RequestInformation\Application\CommandHandler\UpdateRequestInformationStatusHandler;
use App\RequestInformation\Application\Query\GetRequestSummaryQuery;
use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use App\RequestInformation\Domain\Repository\RequestInformationStatusRepositoryInterface;
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
use Symfony\Component\Routing\Requirement\Requirement;

class RequestInformationController extends AbstractController
{
    /**
     * @throws ExceptionInterface
     */
    #[Route('/requests-information', name: 'create_request_information', methods: ['POST'])]
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
        $organizationId = $request->headers->get('x-org-id');

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
    #[Route('/requests-information/summary', name: 'requests_information_summary', methods: ['GET'])]
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



    #[Route('/requests-information', name: 'requests_information_list', methods: ['GET'])]
    #[OA\Get(
        summary: "Lista peticiones por estado, paginadas",
        tags: ['RequestInformation'],
        parameters: [
            new OA\Parameter(
                name: "status",
                description: "Estado a consultar (NEW, IN_PROGRESS, RECONTACT, WON, LOST)",
                in: "query",
                required: false,
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

        $result = $repo->getAllPaginated($status, $page, $limit);

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

    #[Route('/requests-information/{id}', name: 'get_request_information', methods: ['GET'])]
    #[OA\Get(
        summary: "Obtener una petición de información por ID",
        tags: ['RequestInformation'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la petición de información",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Petición encontrada",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "string"),
                        new OA\Property(property: "firstName", type: "string"),
                        new OA\Property(property: "lastName", type: "string"),
                        new OA\Property(property: "email", type: "string"),
                        new OA\Property(property: "phone", type: "string"),
                        new OA\Property(property: "city", type: "string"),
                        new OA\Property(property: "programInterestId", type: "string"),
                        new OA\Property(property: "leadOriginId", type: "string"),
                        new OA\Property(property: "assigneeId", type: "string", nullable: true),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                        new OA\Property(property: "updatedAt", type: "string", format: "date-time", nullable: true),
                        new OA\Property(property: "status", type: "string"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Petición no encontrada",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Request not found")
                    ]
                )
            )
        ]
    )]
    public function getById(
        string $id,
        RequestInformationRepositoryInterface $repo
    ): JsonResponse {
        $request = $repo->findById($id);

        if (!$request) {
            return $this->json(['error' => true, 'message' => 'Request not found'], 404);
        }

        // Mapear los datos a un array limpio para API
        $data = [
            'id' => $request->getId(),
            'firstName' => $request->getFirstName(),
            'lastName' => $request->getLastName(),
            'email' => $request->getEmail()->getValue(),
            'phone' => $request->getPhone()->getValue(),
            'city' => $request->getCity(),
            'programInterestId' => $request->getProgramInterestId(),
            'leadOriginId' => $request->getLeadOriginId(),
            'assigneeId' => $request->getAssigneeId(),
            'createdAt' => $request->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $request->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'status' => $request->getStatus()->getCode(),
        ];

        return $this->json($data);
    }

    #[Route('/requests-information/by-assignee/{assigneeId}', name: 'requests_by_assignee', methods: ['GET'])]
    #[OA\Get(
        summary: "Lista peticiones asignadas a un responsable específico",
        tags: ['RequestInformation'],
        parameters: [
            new OA\Parameter(
                name: "assigneeId",
                description: "ID del responsable",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "status",
                description: "Estado a consultar (opcional)",
                in: "query",
                required: false,
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
                description: "Listado paginado de peticiones por responsable",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "string"),
                                new OA\Property(property: "firstName", type: "string"),
                                new OA\Property(property: "lastName", type: "string"),
                                new OA\Property(property: "email", type: "string"),
                                new OA\Property(property: "phone", type: "string"),
                                new OA\Property(property: "city", type: "string"),
                                new OA\Property(property: "assigneeId", type: "string"),
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
    public function listByAssignee(
        string $assigneeId,
        Request $request,
        RequestInformationRepositoryInterface $repo
    ): JsonResponse {
        $organizationId = $request->headers->get('x-org-id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $status = $request->query->get('status');
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 10);

        $result = $repo->findByAssigneeId($assigneeId, $status, $page, $limit);

        // Mapear los datos a un array limpio para API
        $data = array_map(function($item) {
            return [
                'id' => $item->getId(),
                'firstName' => $item->getFirstName(),
                'lastName' => $item->getLastName(),
                'email' => $item->getEmail()->getValue(),
                'phone' => $item->getPhone()->getValue(),
                'city' => $item->getCity(),
                'assigneeId' => $item->getAssigneeId(),
                'programInterestId' => $item->getProgramInterestId(),
                'leadOriginId' => $item->getLeadOriginId(),
                'createdAt' => $item->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $item->getUpdatedAt()?->format('Y-m-d H:i:s'),
                'status' => $item->getStatus()->getCode(),
            ];
        }, $result);

        return $this->json([
            'data' => $data,
            'page' => $page,
            'limit' => $limit,
            'count' => count($data)
        ]);
    }

    #[Route('/requests-information/{id}/status', name: 'change_request_status', methods: ['PATCH'])]
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
    #[Route('/requests-information/{id}/notes', name: 'add_request_note', methods: ['POST'])]
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

    #[Route('/requests-information/{id}/notes', name: 'list_request_notes', methods: ['GET'])]
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

    #[Route('/requests-information/notes/{noteId}', name: 'update_request_note', methods: ['PATCH'])]
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

    #[Route('/requests-information/notes/{noteId}', name: 'delete_request_note', methods: ['DELETE'])]
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

    #[Route('/requests-information/status', name: 'list_request_status', methods: ['GET'])]
    #[OA\Get(
        summary: "Obtener todos los estados disponibles",
        tags: ['RequestStatus'],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de estados disponibles",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "status",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string"),
                                    new OA\Property(property: "code", type: "string"),
                                    new OA\Property(property: "label", type: "string")
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function listStatus(Request $request, RequestInformationStatusRepositoryInterface $repo): JsonResponse
    {
        $organizationId = $request->headers->get('x-org-id');

        $status = $repo->findByOrganizationId($organizationId);
        $data = array_map(fn($s) => [
            'id' => $s->getId(),
            'code' => $s->getCode(),
            'name' => $s->getName(),
            'sort' => $s->getSort(),
        ], $status);

        return $this->json(['status' => $data]);
    }


    #[Route('/requests-information/status', name: 'create_request_status', methods: ['POST'])]
    #[OA\Post(
        summary: "Crear un nuevo estado",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["code", "label"],
                properties: [
                    new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "label", type: "string")
                ]
            )
        ),
        tags: ['RequestStatus'],
        responses: [
            new OA\Response(
                response: 201,
                description: "Estado creado correctamente"
            )
        ]
    )]
    public function createStatus(Request $request, AddRequestInformationStatusHandler $handler): JsonResponse
    {
        $organizationId = $request->headers->get('x-org-id');

        $data = json_decode($request->getContent(), true);

        if (empty($data['code']) || empty($data['name'])) {
            return $this->json(['error' => true, 'message' => 'code and name are required'], 400);
        }

        $command = new AddRequestInformationStatusCommand($data['coder'], $data['name'], $organizationId);
        $handler->__invoke($command);

        return $this->json(['success' => true], 201);
    }

    #[Route('/requests-information/status/{id}', name: 'update_request_status', requirements: ['id' => Requirement::UUID], methods: ['PATCH'])]
    #[OA\Patch(
        summary: "Actualizar un estado (code, name o sort)",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "code", type: "string", nullable: true),
                    new OA\Property(property: "name", type: "string", nullable: true),
                    new OA\Property(property: "sort", type: "integer", nullable: true)
                ]
            )
        ),
        tags: ['RequestStatus'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID del estado",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: "Estado actualizado correctamente"),
            new OA\Response(response: 400, description: "Datos inválidos"),
            new OA\Response(response: 404, description: "Estado no encontrado")
        ]
    )]
    public function updateStatus(
        string $id,
        Request $request,
        UpdateRequestInformationStatusHandler $handler
    ): JsonResponse {
        $orgId = $request->headers->get('x-org-id');
        if (!$orgId) {
            return $this->json(['error' => true, 'message' => 'x-org-id header is required'], 400);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        // Nada que actualizar
        if (!array_key_exists('code', $data) && !array_key_exists('name', $data) && !array_key_exists('sort', $data)) {
            return $this->json(['error' => true, 'message' => 'Nothing to update'], 400);
        }

        $command = new UpdateRequestInformationStatusCommand(
            $id,
            $orgId,
            $data['code'] ?? null,
            $data['name'] ?? null,
            isset($data['sort']) ? (int)$data['sort'] : null
        );

        $handler->__invoke($command);

        return $this->json(['success' => true]);
    }

    #[Route('/requests-information/status/reorder', name: 'reorder_request_status', methods: ['PATCH'])]
    #[OA\Patch(
        summary: "Reordenar estados por drag & drop (bulk sort update)",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["items"],
                properties: [
                    new OA\Property(
                        property: "items",
                        type: "array",
                        items: new OA\Items(
                            required: ["id", "sort"],
                            properties: [
                                new OA\Property(property: "id", type: "string"),
                                new OA\Property(property: "sort", type: "integer")
                            ]
                        )
                    )
                ]
            )
        ),
        tags: ['RequestStatus'],
        responses: [
            new OA\Response(response: 200, description: "Orden actualizado"),
            new OA\Response(response: 400, description: "Datos inválidos")
        ]
    )]
    public function reorderStatus(
        Request $request,
        ReorderRequestInformationStatusesHandler $handler
    ): JsonResponse {
        $orgId = $request->headers->get('x-org-id');

        if (!$orgId) {
            return $this->json(['error' => true, 'message' => 'x-org-id header is required'], 400);
        }

        $payload = json_decode($request->getContent(), true) ?? [];
        $items = $payload['items'] ?? null;

        if (!is_array($items) || empty($items)) {
            return $this->json(['error' => true, 'message' => 'items is required and must be a non-empty array'], 400);
        }

        // Normaliza: [{id, sort}]
        $normalized = array_map(function ($row) {
            if (!isset($row['id'], $row['sort'])) {
                throw new \InvalidArgumentException('Each item must have id and sort');
            }
            return [
                'id'   => (string)$row['id'],
                'sort' => (int)$row['sort'],
            ];
        }, $items);

        $command = new ReorderRequestInformationStatusesCommand(
            $orgId,
            $normalized
        );

        $handler->__invoke($command);

        return $this->json(['success' => true]);
    }

    #[Route('/requests-information/assignment-rules', name: 'list_assignment_rules', methods: ['GET'])]
    #[OA\Get(
        summary: "Get all assignment rules for the organization",
        tags: ['AssignmentRules'],
        parameters: [
            new OA\Parameter(
                name: "X-Org-Id",
                description: "Organization ID",
                in: "header",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of assignment rules",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string"),
                                    new OA\Property(property: "name", type: "string"),
                                    new OA\Property(property: "description", type: "string", nullable: true),
                                    new OA\Property(property: "active", type: "boolean"),
                                    new OA\Property(property: "priority", type: "integer"),
                                    new OA\Property(property: "conditions", type: "array", items: new OA\Items(type: "object")),
                                    new OA\Property(property: "assignmentType", type: "string"),
                                    new OA\Property(property: "assigneeIds", type: "array", items: new OA\Items(type: "string")),
                                    new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                                    new OA\Property(property: "updatedAt", type: "string", format: "date-time")
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function listAssignmentRules(Request $request): JsonResponse
    {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        // Mock data for now - in real implementation, you would fetch from repository
        $mockRules = [
            [
                'id' => '1',
                'name' => 'Distribución Equitativa',
                'description' => 'Asigna peticiones de forma equitativa entre todos los representantes activos',
                'active' => true,
                'priority' => 1,
                'conditions' => [],
                'assignmentType' => 'round_robin',
                'assigneeIds' => ['1', '2', '3'],
                'createdAt' => '2024-01-01T10:00:00Z',
                'updatedAt' => '2024-01-01T10:00:00Z'
            ],
            [
                'id' => '2',
                'name' => 'Peticiones VIP',
                'description' => 'Asigna peticiones de alto valor al manager',
                'active' => true,
                'priority' => 2,
                'conditions' => [
                    [
                        'field' => 'amount',
                        'operator' => 'greater_than',
                        'value' => 10000
                    ]
                ],
                'assignmentType' => 'manual',
                'assigneeIds' => ['3'],
                'createdAt' => '2024-01-01T10:00:00Z',
                'updatedAt' => '2024-01-01T10:00:00Z'
            ]
        ];

        return $this->json(['data' => $mockRules]);
    }

    #[Route('/requests-information/assignment-rules', name: 'create_assignment_rule', methods: ['POST'])]
    #[OA\Post(
        summary: "Create a new assignment rule",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "assignmentType", "assigneeIds"],
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "description", type: "string", nullable: true),
                    new OA\Property(property: "active", type: "boolean", default: true),
                    new OA\Property(property: "priority", type: "integer", default: 1),
                    new OA\Property(property: "conditions", type: "array", items: new OA\Items(type: "object")),
                    new OA\Property(property: "assignmentType", type: "string", enum: ["round_robin", "load_balanced", "skill_based", "manual"]),
                    new OA\Property(property: "assigneeIds", type: "array", items: new OA\Items(type: "string"))
                ]
            )
        ),
        tags: ['AssignmentRules'],
        parameters: [
            new OA\Parameter(
                name: "X-Org-Id",
                description: "Organization ID",
                in: "header",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: "Assignment rule created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "string"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "description", type: "string", nullable: true),
                        new OA\Property(property: "active", type: "boolean"),
                        new OA\Property(property: "priority", type: "integer"),
                        new OA\Property(property: "conditions", type: "array", items: new OA\Items(type: "object")),
                        new OA\Property(property: "assignmentType", type: "string"),
                        new OA\Property(property: "assigneeIds", type: "array", items: new OA\Items(type: "string")),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                        new OA\Property(property: "updatedAt", type: "string", format: "date-time")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Invalid input data")
        ]
    )]
    public function createAssignmentRule(Request $request): JsonResponse
    {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => true, 'message' => 'Invalid JSON data'], 400);
        }

        // Validate required fields
        if (empty($data['name']) || empty($data['assignmentType']) || empty($data['assigneeIds'])) {
            return $this->json(['error' => true, 'message' => 'Missing required fields: name, assignmentType, assigneeIds'], 400);
        }

        // Mock creation - in real implementation, you would save to repository
        $newRule = [
            'id' => uniqid(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'active' => $data['active'] ?? true,
            'priority' => $data['priority'] ?? 1,
            'conditions' => $data['conditions'] ?? [],
            'assignmentType' => $data['assignmentType'],
            'assigneeIds' => $data['assigneeIds'],
            'createdAt' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s\Z')
        ];

        return $this->json($newRule, 201);
    }

    #[Route('/requests-information/assignment-rules/{id}', name: 'update_assignment_rule', methods: ['PATCH'])]
    #[OA\Patch(
        summary: "Update an assignment rule",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", nullable: true),
                    new OA\Property(property: "description", type: "string", nullable: true),
                    new OA\Property(property: "active", type: "boolean", nullable: true),
                    new OA\Property(property: "priority", type: "integer", nullable: true),
                    new OA\Property(property: "conditions", type: "array", items: new OA\Items(type: "object"), nullable: true),
                    new OA\Property(property: "assignmentType", type: "string", enum: ["round_robin", "load_balanced", "skill_based", "manual"], nullable: true),
                    new OA\Property(property: "assigneeIds", type: "array", items: new OA\Items(type: "string"), nullable: true)
                ]
            )
        ),
        tags: ['AssignmentRules'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Assignment rule ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "X-Org-Id",
                description: "Organization ID",
                in: "header",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Assignment rule updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "string"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "description", type: "string", nullable: true),
                        new OA\Property(property: "active", type: "boolean"),
                        new OA\Property(property: "priority", type: "integer"),
                        new OA\Property(property: "conditions", type: "array", items: new OA\Items(type: "object")),
                        new OA\Property(property: "assignmentType", type: "string"),
                        new OA\Property(property: "assigneeIds", type: "array", items: new OA\Items(type: "string")),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                        new OA\Property(property: "updatedAt", type: "string", format: "date-time")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Assignment rule not found"),
            new OA\Response(response: 400, description: "Invalid input data")
        ]
    )]
    public function updateAssignmentRule(string $id, Request $request): JsonResponse
    {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => true, 'message' => 'Invalid JSON data'], 400);
        }

        // Mock update - in real implementation, you would find and update in repository
        $updatedRule = [
            'id' => $id,
            'name' => $data['name'] ?? 'Updated Rule',
            'description' => $data['description'] ?? null,
            'active' => $data['active'] ?? true,
            'priority' => $data['priority'] ?? 1,
            'conditions' => $data['conditions'] ?? [],
            'assignmentType' => $data['assignmentType'] ?? 'round_robin',
            'assigneeIds' => $data['assigneeIds'] ?? [],
            'createdAt' => '2024-01-01T10:00:00Z',
            'updatedAt' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s\Z')
        ];

        return $this->json($updatedRule);
    }

    #[Route('/requests-information/assignment-rules/{id}', name: 'delete_assignment_rule', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Delete an assignment rule",
        tags: ['AssignmentRules'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Assignment rule ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "X-Org-Id",
                description: "Organization ID",
                in: "header",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Assignment rule deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Assignment rule deleted successfully")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Assignment rule not found")
        ]
    )]
    public function deleteAssignmentRule(string $id, Request $request): JsonResponse
    {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        // Mock deletion - in real implementation, you would find and delete from repository
        // For now, we'll just return success
        return $this->json([
            'success' => true,
            'message' => 'Assignment rule deleted successfully'
        ]);
    }

    #[Route('/requests-information/assignee/{assigneeId}/period', name: 'requests_by_assignee_and_period', methods: ['GET'])]
    #[OA\Get(
        summary: "Lista peticiones asignadas a un responsable en un período específico",
        tags: ['RequestInformation'],
        parameters: [
            new OA\Parameter(
                name: "assigneeId",
                description: "ID del responsable",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "from",
                description: "Fecha de inicio (YYYY-MM-DD)",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", format: "date")
            ),
            new OA\Parameter(
                name: "to",
                description: "Fecha de fin (YYYY-MM-DD)",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", format: "date")
            ),
            new OA\Parameter(
                name: "status",
                description: "Estado a consultar (opcional)",
                in: "query",
                required: false,
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
                description: "Listado paginado de peticiones por responsable y período",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "string"),
                                new OA\Property(property: "firstName", type: "string"),
                                new OA\Property(property: "lastName", type: "string"),
                                new OA\Property(property: "email", type: "string"),
                                new OA\Property(property: "phone", type: "string"),
                                new OA\Property(property: "city", type: "string"),
                                new OA\Property(property: "assigneeId", type: "string"),
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
    public function listByAssigneeAndPeriod(
        string $assigneeId,
        Request $request,
        RequestInformationRepositoryInterface $repo
    ): JsonResponse {
        $organizationId = $request->headers->get('x-org-id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $from = $request->query->get('from');
        $to = $request->query->get('to');
        $status = $request->query->get('status');
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 10);

        $fromDate = $from ? \DateTimeImmutable::createFromFormat('Y-m-d', $from) : null;
        $toDate = $to ? \DateTimeImmutable::createFromFormat('Y-m-d', $to) : null;

        $result = $repo->findByAssigneeAndDateRange($assigneeId, $organizationId, $fromDate, $toDate, $status, $page, $limit);

        // Mapear los datos a un array limpio para API
        $data = array_map(function($item) {
            return [
                'id' => $item->getId(),
                'firstName' => $item->getFirstName(),
                'lastName' => $item->getLastName(),
                'email' => $item->getEmail()->getValue(),
                'phone' => $item->getPhone()->getValue(),
                'city' => $item->getCity(),
                'assigneeId' => $item->getAssigneeId(),
                'programInterestId' => $item->getProgramInterestId(),
                'leadOriginId' => $item->getLeadOriginId(),
                'createdAt' => $item->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $item->getUpdatedAt()?->format('Y-m-d H:i:s'),
                'status' => $item->getStatus(),
            ];
        }, $result);

        return $this->json([
            'data' => $data,
            'page' => $page,
            'limit' => $limit,
            'count' => count($data)
        ]);
    }

}
