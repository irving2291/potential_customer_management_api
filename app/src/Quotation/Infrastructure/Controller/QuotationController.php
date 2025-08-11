<?php

namespace App\Quotation\Infrastructure\Controller;

use App\Quotation\Application\Command\AddQuotationDetailCommand;
use App\Quotation\Application\Command\EditQuotationDetailCommand;
use App\Quotation\Application\Command\RemoveQuotationDetailCommand;
use App\Quotation\Domain\Repository\QuotationRepositoryInterface;
use App\Quotation\Domain\ValueObject\QuotationStatus;
use App\Quotation\Application\Command\CreateQuotationCommand;
use App\Quotation\Application\Command\ChangeQuotationStatusCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use OpenApi\Attributes as OA;

class QuotationController extends AbstractController
{
    #[Route('/quotations', name: 'create_quotation', methods: ['POST'])]
    #[OA\Post(
        summary: "Create a new quotation",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["requestInformationId", "details"],
                properties: [
                    new OA\Property(property: "requestInformationId", type: "string"),
                    new OA\Property(
                        property: "details",
                        type: "array",
                        items: new OA\Items(
                            required: ["description", "unitPrice", "quantity"],
                            properties: [
                                new OA\Property(property: "description", type: "string"),
                                new OA\Property(property: "unitPrice", type: "number", format: "float"),
                                new OA\Property(property: "quantity", type: "integer"),
                                new OA\Property(property: "total", type: "number", format: "float")
                            ]
                        )
                    ),
                    new OA\Property(property: "status", type: "string", enum: ["creating", "sent", "accepted", "rejected"], nullable: true)
                ]
            )
        ),
        tags: ['Quotation'],
        responses: [
            new OA\Response(response: 201, description: "Quotation created successfully"),
            new OA\Response(response: 400, description: "Invalid request")
        ]
    )]
    public function create(
        Request $request,
        MessageBusInterface $commandBus
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (
            empty($data['requestInformationId']) ||
            !is_array($data['details']) ||
            count($data['details']) === 0
        ) {
            return $this->json(['error' => true, 'message' => 'Invalid data.'], 400);
        }

        $command = new CreateQuotationCommand(
            $data['requestInformationId'],
            $data['details'],
            $data['status'] ?? null
        );
        $commandBus->dispatch($command);

        return $this->json(['success' => true], 201);
    }

    #[Route('/quotations/{id}', name: 'get_quotation_by_id', methods: ['GET'])]
    #[OA\Get(
        summary: "Get quotation by its ID",
        tags: ['Quotation'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "The Quotation ID (UUID)",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Quotation data",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "string"),
                        new OA\Property(property: "requestInformationId", type: "string"),
                        new OA\Property(
                            property: "details",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "description", type: "string"),
                                    new OA\Property(property: "unitPrice", type: "number", format: "float"),
                                    new OA\Property(property: "quantity", type: "integer"),
                                    new OA\Property(property: "total", type: "number", format: "float")
                                ]
                            )
                        ),
                        new OA\Property(property: "status", type: "string"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                        new OA\Property(property: "updatedAt", type: "string", format: "date-time", nullable: true),
                        new OA\Property(property: "deletedAt", type: "string", format: "date-time", nullable: true)
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Quotation not found"
            )
        ]
    )]
    public function getQuotationById(
        string $id,
        QuotationRepositoryInterface $repo
    ): JsonResponse {
        $quotation = $repo->findById($id);

        if (!$quotation) {
            return $this->json(['error' => true, 'message' => 'Quotation not found'], 404);
        }

        return $this->json([
            'id' => $quotation->getId(),
            'requestInformationId' => $quotation->getRequestInformationId(),
            'details' => array_map(fn($d) => [
                'description' => $d->description,
                'unitPrice'   => $d->unitPrice,
                'quantity'    => $d->quantity,
                'total'       => $d->total
            ], $quotation->getDetails()),
            'status' => $quotation->getStatus()->value,
            'createdAt' => $quotation->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $quotation->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'deletedAt' => $quotation->getDeletedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/quotations/request-information/{requestInformationId}', name: 'get_quotation_by_request_information', methods: ['GET'])]
    #[OA\Get(
        summary: "Get quotation by RequestInformation ID",
        tags: ['Quotation'],
        parameters: [
            new OA\Parameter(
                name: "requestInformationId",
                description: "The RequestInformation ID (UUID)",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Quotation data",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "string"),
                        new OA\Property(property: "requestInformationId", type: "string"),
                        new OA\Property(
                            property: "details",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "description", type: "string"),
                                    new OA\Property(property: "unitPrice", type: "number", format: "float"),
                                    new OA\Property(property: "quantity", type: "integer"),
                                    new OA\Property(property: "total", type: "number", format: "float")
                                ]
                            )
                        ),
                        new OA\Property(property: "status", type: "string"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                        new OA\Property(property: "updatedAt", type: "string", format: "date-time", nullable: true),
                        new OA\Property(property: "deletedAt", type: "string", format: "date-time", nullable: true)
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Quotation not found"
            )
        ]
    )]
    public function getQuotationByRequestInformation(
        string $requestInformationId,
        QuotationRepositoryInterface $repo
    ): JsonResponse {
        $quotation = $repo->findByRequestInformationId($requestInformationId);

        if (!$quotation) {
            return $this->json(['error' => true, 'message' => 'Quotation not found'], 404);
        }

        return $this->json([
            'id' => $quotation->getId(),
            'requestInformationId' => $quotation->getRequestInformationId(),
            'details' => array_map(fn($d) => [
                'description' => $d->description,
                'unitPrice'   => $d->unitPrice,
                'quantity'    => $d->quantity,
                'total'       => $d->total
            ], $quotation->getDetails()),
            'status' => $quotation->getStatus()->value,
            'createdAt' => $quotation->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $quotation->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'deletedAt' => $quotation->getDeletedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/quotations/{id}/status', name: 'change_quotation_status', methods: ['PATCH'])]
    #[OA\Patch(
        summary: "Change status of a quotation",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["creating", "sent", "accepted", "rejected"])
                ]
            )
        ),
        tags: ['Quotation'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "The Quotation ID (UUID)",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Status changed"),
            new OA\Response(response: 400, description: "Invalid status"),
            new OA\Response(response: 404, description: "Quotation not found")
        ]
    )]
    public function changeStatus(
        string $id,
        Request $request,
        MessageBusInterface $commandBus
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $status = $data['status'] ?? null;

        if (!$status || !in_array($status, array_map(fn($s) => $s->value, QuotationStatus::cases()), true)) {
            return $this->json(['error' => true, 'message' => 'Invalid status'], 400);
        }

        $command = new ChangeQuotationStatusCommand($id, $status);
        $commandBus->dispatch($command);

        return $this->json(['success' => true]);
    }


    #[Route('/quotations/{id}/details', name: 'add_quotation_detail', methods: ['POST'])]
    #[OA\Post(
        summary: "Add a detail to the quotation",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["description", "unitPrice", "quantity", "total"],
                properties: [
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "unitPrice", type: "number", format: "float"),
                    new OA\Property(property: "quantity", type: "integer"),
                    new OA\Property(property: "total", type: "number", format: "float")
                ]
            )
        ),
        tags: ['QuotationDetail'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Quotation ID (UUID)",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: "Detail added successfully"),
            new OA\Response(response: 404, description: "Quotation not found"),
            new OA\Response(response: 400, description: "Invalid data"),
        ]
    )]
    public function addDetail(
        string $id,
        Request $request,
        MessageBusInterface $commandBus
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $command = new AddQuotationDetailCommand(
            $id,
            $data['description'],
            (float)$data['unitPrice'],
            (int)$data['quantity'],
            (float)$data['total']
        );
        $commandBus->dispatch($command);
        return $this->json(['success' => true]);
    }

    #[Route('/quotations/{id}/details/{index}', name: 'edit_quotation_detail', methods: ['PATCH'])]
    #[OA\Patch(
        summary: "Edit a detail of the quotation by index",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["description", "unitPrice", "quantity", "total"],
                properties: [
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "unitPrice", type: "number", format: "float"),
                    new OA\Property(property: "quantity", type: "integer"),
                    new OA\Property(property: "total", type: "number", format: "float")
                ]
            )
        ),
        tags: ['QuotationDetail'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Quotation ID (UUID)",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "index",
                description: "Index of the detail to edit (zero-based)",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: "Detail updated successfully"),
            new OA\Response(response: 404, description: "Quotation or detail not found"),
            new OA\Response(response: 400, description: "Invalid data"),
        ]
    )]
    public function editDetail(
        string $id,
        int $index,
        Request $request,
        MessageBusInterface $commandBus
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $command = new EditQuotationDetailCommand(
            $id,
            $index,
            $data['description'],
            (float)$data['unitPrice'],
            (int)$data['quantity'],
            (float)$data['total']
        );
        $commandBus->dispatch($command);
        return $this->json(['success' => true]);
    }

    #[Route('/quotations/{id}/details/{index}', name: 'remove_quotation_detail', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Remove a detail from the quotation by index",
        tags: ['QuotationDetail'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Quotation ID (UUID)",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "index",
                description: "Index of the detail to remove (zero-based)",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: "Detail removed successfully"),
            new OA\Response(response: 404, description: "Quotation or detail not found")
        ]
    )]
    public function removeDetail(
        string $id,
        int $index,
        MessageBusInterface $commandBus
    ): JsonResponse {
        $command = new RemoveQuotationDetailCommand($id, $index);
        $commandBus->dispatch($command);
        return $this->json(['success' => true]);
    }

}
