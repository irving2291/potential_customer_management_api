<?php

namespace App\Activation\Infrastructure\Controller;

use App\Activation\Domain\Aggregate\Activation;
use App\Activation\Domain\Repository\ActivationRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class ActivationController extends AbstractController
{
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
    public function listActivations(
        Request $request,
        ActivationRepositoryInterface $activationRepository
    ): JsonResponse {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = max(1, min(100, (int) $request->query->get('perPage', 20)));
        $status = $request->query->get('status');
        $type = $request->query->get('type');
        $search = $request->query->get('search');

        // Mock data for now - replace with actual repository call
        $mockActivations = [
            [
                'id' => '1',
                'title' => 'Promoción Black Friday',
                'description' => 'Descuentos especiales del 50% en todos nuestros servicios durante el Black Friday',
                'type' => 'promotion',
                'status' => 'scheduled',
                'priority' => 'high',
                'channels' => ['email', 'sms', 'whatsapp'],
                'targetAudience' => 'Clientes activos',
                'scheduledFor' => '2024-11-29T09:00:00Z',
                'createdBy' => 'user1',
                'createdAt' => '2024-11-15T10:00:00Z',
                'updatedAt' => null
            ],
            [
                'id' => '2',
                'title' => 'Encuesta de Satisfacción',
                'description' => 'Queremos conocer tu opinión sobre nuestros servicios',
                'type' => 'survey',
                'status' => 'active',
                'priority' => 'medium',
                'channels' => ['email'],
                'targetAudience' => 'Clientes con servicios completados',
                'scheduledFor' => null,
                'createdBy' => 'user2',
                'createdAt' => '2024-11-10T14:30:00Z',
                'updatedAt' => '2024-11-12T09:15:00Z'
            ]
        ];

        // Apply filters
        $filteredActivations = $mockActivations;
        if ($status) {
            $filteredActivations = array_filter($filteredActivations, fn($a) => $a['status'] === $status);
        }
        if ($type) {
            $filteredActivations = array_filter($filteredActivations, fn($a) => $a['type'] === $type);
        }
        if ($search) {
            $search = strtolower($search);
            $filteredActivations = array_filter($filteredActivations, fn($a) => 
                str_contains(strtolower($a['title']), $search) || 
                str_contains(strtolower($a['description']), $search)
            );
        }

        $total = count($filteredActivations);
        $offset = ($page - 1) * $perPage;
        $paginatedActivations = array_slice($filteredActivations, $offset, $perPage);

        return $this->json([
            'data' => array_values($paginatedActivations),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage
        ]);
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
    public function createActivation(
        Request $request,
        ActivationRepositoryInterface $activationRepository
    ): JsonResponse {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['title'], $data['description'], $data['type'], $data['priority'], $data['channels'])) {
            return $this->json(['error' => true, 'message' => 'Missing required fields'], 400);
        }

        // Mock creation - in real implementation, create and save the activation
        $activationId = uniqid();
        $scheduledFor = isset($data['scheduledFor']) ? new \DateTimeImmutable($data['scheduledFor']) : null;

        $mockActivation = [
            'id' => $activationId,
            'title' => $data['title'],
            'description' => $data['description'],
            'type' => $data['type'],
            'status' => 'draft',
            'priority' => $data['priority'],
            'channels' => $data['channels'],
            'targetAudience' => $data['targetAudience'] ?? null,
            'scheduledFor' => $scheduledFor?->format('Y-m-d\TH:i:s\Z'),
            'createdBy' => 'current-user', // Should come from authentication
            'createdAt' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => null
        ];

        return $this->json($mockActivation, 201);
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
    public function getActivation(
        string $id,
        ActivationRepositoryInterface $activationRepository
    ): JsonResponse {
        // Mock data - replace with actual repository call
        if ($id === '1') {
            return $this->json([
                'id' => '1',
                'title' => 'Promoción Black Friday',
                'description' => 'Descuentos especiales del 50% en todos nuestros servicios durante el Black Friday',
                'type' => 'promotion',
                'status' => 'scheduled',
                'priority' => 'high',
                'channels' => ['email', 'sms', 'whatsapp'],
                'targetAudience' => 'Clientes activos',
                'scheduledFor' => '2024-11-29T09:00:00Z',
                'createdBy' => 'user1',
                'createdAt' => '2024-11-15T10:00:00Z',
                'updatedAt' => null
            ]);
        }

        return $this->json(['error' => true, 'message' => 'Activation not found'], 404);
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
    public function updateActivation(
        string $id,
        Request $request,
        ActivationRepositoryInterface $activationRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        // Mock update - in real implementation, find and update the activation
        if ($id === '1') {
            return $this->json([
                'id' => $id,
                'title' => $data['title'] ?? 'Promoción Black Friday',
                'description' => $data['description'] ?? 'Descuentos especiales del 50% en todos nuestros servicios durante el Black Friday',
                'type' => $data['type'] ?? 'promotion',
                'status' => 'draft', // Status doesn't change with regular update
                'priority' => $data['priority'] ?? 'high',
                'channels' => $data['channels'] ?? ['email', 'sms', 'whatsapp'],
                'targetAudience' => $data['targetAudience'] ?? 'Clientes activos',
                'scheduledFor' => $data['scheduledFor'] ?? '2024-11-29T09:00:00Z',
                'createdBy' => 'user1',
                'createdAt' => '2024-11-15T10:00:00Z',
                'updatedAt' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s\Z')
            ]);
        }

        return $this->json(['error' => true, 'message' => 'Activation not found'], 404);
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
    public function changeActivationStatus(
        string $id,
        Request $request,
        ActivationRepositoryInterface $activationRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $newStatus = $data['status'] ?? null;

        if (!$newStatus) {
            return $this->json(['error' => true, 'message' => 'Status is required'], 400);
        }

        // Mock status change - in real implementation, find activation and change status
        if ($id === '1') {
            return $this->json([
                'success' => true,
                'message' => 'Activation status changed successfully',
                'newStatus' => $newStatus
            ]);
        }

        return $this->json(['error' => true, 'message' => 'Activation not found'], 404);
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
    public function deleteActivation(
        string $id,
        ActivationRepositoryInterface $activationRepository
    ): JsonResponse {
        // Mock deletion - in real implementation, find and delete the activation
        if ($id === '1') {
            return $this->json(['success' => true, 'message' => 'Activation deleted successfully']);
        }

        return $this->json(['error' => true, 'message' => 'Activation not found'], 404);
    }
}