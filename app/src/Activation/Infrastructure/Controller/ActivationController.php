<?php

namespace App\Activation\Infrastructure\Controller;

use App\Activation\Application\UseCase\ListActivationsUseCase;
use App\Activation\Application\UseCase\GetActivationUseCase;
use App\Activation\Application\UseCase\CreateActivationUseCase;
use App\Activation\Application\UseCase\UpdateActivationUseCase;
use App\Activation\Application\UseCase\DeleteActivationUseCase;
use App\Activation\Application\UseCase\ChangeActivationStatusUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class ActivationController extends AbstractController
{
    private ListActivationsUseCase $listActivationsUseCase;
    private GetActivationUseCase $getActivationUseCase;
    private CreateActivationUseCase $createActivationUseCase;
    private UpdateActivationUseCase $updateActivationUseCase;
    private DeleteActivationUseCase $deleteActivationUseCase;
    private ChangeActivationStatusUseCase $changeActivationStatusUseCase;

    public function __construct(
        ListActivationsUseCase $listActivationsUseCase,
        GetActivationUseCase $getActivationUseCase,
        CreateActivationUseCase $createActivationUseCase,
        UpdateActivationUseCase $updateActivationUseCase,
        DeleteActivationUseCase $deleteActivationUseCase,
        ChangeActivationStatusUseCase $changeActivationStatusUseCase
    ) {
        $this->listActivationsUseCase = $listActivationsUseCase;
        $this->getActivationUseCase = $getActivationUseCase;
        $this->createActivationUseCase = $createActivationUseCase;
        $this->updateActivationUseCase = $updateActivationUseCase;
        $this->deleteActivationUseCase = $deleteActivationUseCase;
        $this->changeActivationStatusUseCase = $changeActivationStatusUseCase;
    }
    #[Route('/activations', name: 'list_activations', methods: ['GET'])]
    #[OA\Get(
        summary: "List all activations by organization",
        tags: ['Activations'],
        parameters: [
            new OA\Parameter(
                name: "X-Org-Id",
                description: "Organization ID",
                in: "header",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "page",
                description: "Page number",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "perPage",
                description: "Items per page",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 20)
            ),
            new OA\Parameter(
                name: "status",
                description: "Filter by status",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", enum: ["draft", "scheduled", "active", "completed", "cancelled"])
            ),
            new OA\Parameter(
                name: "type",
                description: "Filter by type",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", enum: ["promotion", "announcement", "reminder", "survey"])
            ),
            new OA\Parameter(
                name: "search",
                description: "Search in title and description",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of activations",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string"),
                                    new OA\Property(property: "title", type: "string"),
                                    new OA\Property(property: "description", type: "string"),
                                    new OA\Property(property: "type", type: "string"),
                                    new OA\Property(property: "status", type: "string"),
                                    new OA\Property(property: "priority", type: "string"),
                                    new OA\Property(property: "channels", type: "array", items: new OA\Items(type: "string")),
                                    new OA\Property(property: "targetAudience", type: "string", nullable: true),
                                    new OA\Property(property: "scheduledFor", type: "string", format: "date-time", nullable: true),
                                    new OA\Property(property: "createdBy", type: "string"),
                                    new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                                    new OA\Property(property: "updatedAt", type: "string", format: "date-time", nullable: true)
                                ]
                            )
                        ),
                        new OA\Property(property: "total", type: "integer"),
                        new OA\Property(property: "page", type: "integer"),
                        new OA\Property(property: "perPage", type: "integer")
                    ]
                )
            )
        ]
    )]
    public function listActivations(Request $request): JsonResponse
    {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = max(1, min(100, (int) $request->query->get('perPage', 20)));
        $status = $request->query->get('status');
        $type = $request->query->get('type');
        $search = $request->query->get('search');

        $result = $this->listActivationsUseCase->execute(
            $organizationId,
            $page,
            $perPage,
            $status,
            $type,
            $search
        );

        return $this->json($result);
    }

    #[Route('/activations', name: 'create_activation', methods: ['POST'])]
    #[OA\Post(
        summary: "Create a new activation",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "description", "type", "priority", "channels"],
                properties: [
                    new OA\Property(property: "title", type: "string"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "type", type: "string", enum: ["promotion", "announcement", "reminder", "survey"]),
                    new OA\Property(property: "priority", type: "string", enum: ["low", "medium", "high", "urgent"]),
                    new OA\Property(property: "channels", type: "array", items: new OA\Items(type: "string")),
                    new OA\Property(property: "targetAudience", type: "string", nullable: true),
                    new OA\Property(property: "scheduledFor", type: "string", format: "date-time", nullable: true)
                ]
            )
        ),
        tags: ['Activations'],
        responses: [
            new OA\Response(response: 201, description: "Activation created successfully"),
            new OA\Response(response: 400, description: "Invalid request data")
        ]
    )]
    public function createActivation(Request $request): JsonResponse
    {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['title'], $data['description'], $data['type'], $data['priority'], $data['channels'])) {
            return $this->json(['error' => true, 'message' => 'Missing required fields'], 400);
        }

        try {
            $result = $this->createActivationUseCase->execute(
                $organizationId,
                $data['title'],
                $data['description'],
                $data['type'],
                $data['priority'],
                $data['channels'],
                'current-user', // Should come from authentication
                $data['targetAudience'] ?? null,
                $data['scheduledFor'] ?? null
            );

            return $this->json($result, 201);
        } catch (\Exception $e) {
            return $this->json(['error' => true, 'message' => $e->getMessage()], 400);
        }
    }

    #[Route('/activations/{id}', name: 'get_activation', methods: ['GET'])]
    #[OA\Get(
        summary: "Get activation by ID",
        tags: ['Activations'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Activation ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Activation details"),
            new OA\Response(response: 404, description: "Activation not found")
        ]
    )]
    public function getActivation(string $id): JsonResponse
    {
        $activation = $this->getActivationUseCase->execute($id);

        if (!$activation) {
            return $this->json(['error' => true, 'message' => 'Activation not found'], 404);
        }

        return $this->json($activation);
    }

    #[Route('/activations/{id}', name: 'update_activation', methods: ['PUT'])]
    #[OA\Put(
        summary: "Update activation",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "type", type: "string"),
                    new OA\Property(property: "priority", type: "string"),
                    new OA\Property(property: "channels", type: "array", items: new OA\Items(type: "string")),
                    new OA\Property(property: "targetAudience", type: "string", nullable: true),
                    new OA\Property(property: "scheduledFor", type: "string", format: "date-time", nullable: true)
                ]
            )
        ),
        tags: ['Activations'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Activation ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Activation updated successfully"),
            new OA\Response(response: 404, description: "Activation not found")
        ]
    )]
    public function updateActivation(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $activation = $this->updateActivationUseCase->execute(
            $id,
            $data['title'] ?? '',
            $data['description'] ?? '',
            $data['type'] ?? '',
            $data['priority'] ?? '',
            $data['channels'] ?? [],
            $data['targetAudience'] ?? null,
            $data['scheduledFor'] ?? null
        );

        if (!$activation) {
            return $this->json(['error' => true, 'message' => 'Activation not found'], 404);
        }

        return $this->json($activation);
    }

    #[Route('/activations/{id}/status', name: 'change_activation_status', methods: ['PATCH'])]
    #[OA\Patch(
        summary: "Change activation status",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["draft", "scheduled", "active", "completed", "cancelled"]),
                    new OA\Property(property: "scheduledFor", type: "string", format: "date-time", nullable: true)
                ]
            )
        ),
        tags: ['Activations'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Activation ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Status changed successfully"),
            new OA\Response(response: 400, description: "Invalid status transition"),
            new OA\Response(response: 404, description: "Activation not found")
        ]
    )]
    public function changeActivationStatus(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $newStatus = $data['status'] ?? null;

        if (!$newStatus) {
            return $this->json(['error' => true, 'message' => 'Status is required'], 400);
        }

        try {
            $result = $this->changeActivationStatusUseCase->execute(
                $id,
                $newStatus,
                $data['scheduledFor'] ?? null
            );

            if (!$result) {
                return $this->json(['error' => true, 'message' => 'Activation not found'], 404);
            }

            return $this->json($result);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => true, 'message' => $e->getMessage()], 400);
        } catch (\DomainException $e) {
            return $this->json(['error' => true, 'message' => $e->getMessage()], 400);
        }
    }

    #[Route('/activations/{id}', name: 'delete_activation', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Delete activation",
        tags: ['Activations'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Activation ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Activation deleted successfully"),
            new OA\Response(response: 404, description: "Activation not found")
        ]
    )]
    public function deleteActivation(string $id): JsonResponse
    {
        $deleted = $this->deleteActivationUseCase->execute($id);

        if (!$deleted) {
            return $this->json(['error' => true, 'message' => 'Activation not found'], 404);
        }

        return $this->json(['success' => true, 'message' => 'Activation deleted successfully']);
    }
}