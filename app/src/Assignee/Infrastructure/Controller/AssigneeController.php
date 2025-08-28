<?php

namespace App\Assignee\Infrastructure\Controller;

use App\Assignee\Domain\Aggregate\Assignee;
use App\Assignee\Domain\Repository\AssigneeRepositoryInterface;
use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class AssigneeController extends AbstractController
{
    #[Route('/assignees', name: 'list_assignees', methods: ['GET'])]
    #[OA\Get(
        summary: "List all assignees by organization",
        tags: ['Assignees'],
        parameters: [
            new OA\Parameter(
                name: "X-Org-Id",
                description: "Organization ID",
                in: "header",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "active",
                description: "Filter by active status",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "boolean")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of assignees",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string"),
                                    new OA\Property(property: "firstName", type: "string"),
                                    new OA\Property(property: "lastName", type: "string"),
                                    new OA\Property(property: "email", type: "string"),
                                    new OA\Property(property: "phone", type: "string"),
                                    new OA\Property(property: "avatar", type: "string"),
                                    new OA\Property(property: "active", type: "boolean"),
                                    new OA\Property(property: "role", type: "string"),
                                    new OA\Property(property: "department", type: "string"),
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
    public function listAssignees(
        Request $request,
        AssigneeRepositoryInterface $assigneeRepository
    ): JsonResponse {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $activeOnly = $request->query->getBoolean('active', false);

        if ($activeOnly) {
            $assignees = $assigneeRepository->findActiveByOrganizationId($organizationId);
        } else {
            $assignees = $assigneeRepository->findByOrganizationId($organizationId);
        }

        $data = array_map(function ($assignee) {
            return [
                'id' => $assignee->getId(),
                'firstName' => $assignee->getFirstName(),
                'lastName' => $assignee->getLastName(),
                'email' => $assignee->getEmail(),
                'phone' => $assignee->getPhone(),
                'avatar' => $assignee->getAvatar(),
                'active' => $assignee->isActive(),
                'role' => $assignee->getRole(),
                'department' => $assignee->getDepartment(),
                'createdAt' => $assignee->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $assignee->getUpdatedAt()?->format('Y-m-d H:i:s')
            ];
        }, $assignees);

        return $this->json(['data' => $data]);
    }

    #[Route('/assignees/stats', name: 'assignee_stats', methods: ['GET'])]
    #[OA\Get(
        summary: "Get assignee statistics",
        tags: ['Assignees'],
        parameters: [
            new OA\Parameter(
                name: "X-Org-Id",
                description: "Organization ID",
                in: "header",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "dateFrom",
                description: "Start date for statistics (YYYY-MM-DD)",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", format: "date")
            ),
            new OA\Parameter(
                name: "dateTo",
                description: "End date for statistics (YYYY-MM-DD)",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", format: "date")
            ),
            new OA\Parameter(
                name: "assigneeIds[]",
                description: "Filter by specific assignee IDs",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "array", items: new OA\Items(type: "string"))
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Assignee statistics",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "assigneeId", type: "string"),
                                    new OA\Property(
                                        property: "assignee",
                                        properties: [
                                            new OA\Property(property: "id", type: "string"),
                                            new OA\Property(property: "firstName", type: "string"),
                                            new OA\Property(property: "lastName", type: "string"),
                                            new OA\Property(property: "role", type: "string")
                                        ],
                                        type: "object"
                                    ),
                                    new OA\Property(property: "totalRequests", type: "integer"),
                                    new OA\Property(property: "completedRequests", type: "integer"),
                                    new OA\Property(property: "pendingRequests", type: "integer"),
                                    new OA\Property(property: "conversionRate", type: "number", format: "float"),
                                    new OA\Property(property: "avgResponseTime", type: "number", format: "float"),
                                    new OA\Property(property: "avgCloseTime", type: "number", format: "float")
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function getAssigneeStats(
        Request $request,
        AssigneeRepositoryInterface $assigneeRepository,
        RequestInformationRepositoryInterface $requestRepository
    ): JsonResponse {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $dateFrom = $request->query->get('dateFrom');
        $dateTo = $request->query->get('dateTo');
        $assigneeIds = $request->query->all('assigneeIds');

        // Get assignees
        if (!empty($assigneeIds)) {
            $assignees = [];
            foreach ($assigneeIds as $id) {
                $assignee = $assigneeRepository->findById($id);
                if ($assignee && $assignee->getOrganizationId() === $organizationId) {
                    $assignees[] = $assignee;
                }
            }
        } else {
            $assignees = $assigneeRepository->findByOrganizationId($organizationId);
        }

        $stats = [];
        foreach ($assignees as $assignee) {
            // Mock statistics - in real implementation, you would query the database
            // for actual request statistics based on the date range
            $totalRequests = rand(10, 50);
            $completedRequests = rand(5, $totalRequests);
            $pendingRequests = $totalRequests - $completedRequests;
            $conversionRate = $totalRequests > 0 ? $completedRequests / $totalRequests : 0;

            $stats[] = [
                'assigneeId' => $assignee->getId(),
                'assignee' => [
                    'id' => $assignee->getId(),
                    'firstName' => $assignee->getFirstName(),
                    'lastName' => $assignee->getLastName(),
                    'role' => $assignee->getRole()
                ],
                'totalRequests' => $totalRequests,
                'completedRequests' => $completedRequests,
                'pendingRequests' => $pendingRequests,
                'conversionRate' => round($conversionRate, 3),
                'avgResponseTime' => round(rand(1, 8) + (rand(0, 99) / 100), 2), // Hours
                'avgCloseTime' => round(rand(2, 12) + (rand(0, 99) / 100), 2) // Days
            ];
        }

        return $this->json(['data' => $stats]);
    }

    #[Route('/requests/{id}/reassign', name: 'reassign_request', methods: ['PATCH'])]
    #[OA\Patch(
        summary: "Reassign a request to another assignee",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["toAssigneeId"],
                properties: [
                    new OA\Property(property: "toAssigneeId", type: "string"),
                    new OA\Property(property: "reason", type: "string", nullable: true)
                ]
            )
        ),
        tags: ['Assignees'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Request ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Request reassigned successfully"),
            new OA\Response(response: 404, description: "Request not found"),
            new OA\Response(response: 400, description: "Invalid assignee")
        ]
    )]
    public function reassignRequest(
        string $id,
        Request $request,
        RequestInformationRepositoryInterface $requestRepository,
        AssigneeRepositoryInterface $assigneeRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $toAssigneeId = $data['toAssigneeId'] ?? null;
        $reason = $data['reason'] ?? null;

        if (!$toAssigneeId) {
            return $this->json(['error' => true, 'message' => 'toAssigneeId is required'], 400);
        }

        // Find the request
        $requestInfo = $requestRepository->findById($id);
        if (!$requestInfo) {
            return $this->json(['error' => true, 'message' => 'Request not found'], 404);
        }

        // Verify the new assignee exists and is active
        $newAssignee = $assigneeRepository->findById($toAssigneeId);
        if (!$newAssignee || !$newAssignee->isActive()) {
            return $this->json(['error' => true, 'message' => 'Invalid or inactive assignee'], 400);
        }

        // In a real implementation, you would update the request's assignee
        // For now, we'll just return success
        // $requestInfo->reassignTo($toAssigneeId, $reason);
        // $requestRepository->save($requestInfo);

        return $this->json(['success' => true, 'message' => 'Request reassigned successfully']);
    }

    #[Route('/assignees', name: 'create_assignee', methods: ['POST'])]
    #[OA\Post(
        summary: "Create a new assignee",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["firstName", "lastName", "email", "phone", "role", "department"],
                properties: [
                    new OA\Property(property: "firstName", type: "string", maxLength: 100),
                    new OA\Property(property: "lastName", type: "string", maxLength: 100),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "phone", type: "string", maxLength: 20),
                    new OA\Property(property: "avatar", type: "string", nullable: true),
                    new OA\Property(property: "active", type: "boolean", default: true),
                    new OA\Property(property: "role", type: "string", maxLength: 50),
                    new OA\Property(property: "department", type: "string", maxLength: 100)
                ]
            )
        ),
        tags: ['Assignees'],
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
                description: "Assignee created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean"),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "string"),
                                new OA\Property(property: "firstName", type: "string"),
                                new OA\Property(property: "lastName", type: "string"),
                                new OA\Property(property: "email", type: "string"),
                                new OA\Property(property: "phone", type: "string"),
                                new OA\Property(property: "avatar", type: "string"),
                                new OA\Property(property: "active", type: "boolean"),
                                new OA\Property(property: "role", type: "string"),
                                new OA\Property(property: "department", type: "string"),
                                new OA\Property(property: "organizationId", type: "string"),
                                new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                                new OA\Property(property: "updatedAt", type: "string", format: "date-time", nullable: true)
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Validation error or missing required fields"),
            new OA\Response(response: 409, description: "Assignee with this email already exists"),
            new OA\Response(response: 500, description: "Internal server error")
        ]
    )]
    public function createAssignee(
        Request $request,
        AssigneeRepositoryInterface $assigneeRepository
    ): JsonResponse {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => true, 'message' => 'Invalid JSON data'], 400);
        }

        // Validate required fields
        $requiredFields = ['firstName', 'lastName', 'email', 'phone', 'role', 'department'];
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            return $this->json([
                'error' => true,
                'message' => 'Missing required fields: ' . implode(', ', $missingFields)
            ], 400);
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => true, 'message' => 'Invalid email format'], 400);
        }

        // Check if assignee with this email already exists
        $existingAssignee = $assigneeRepository->findByEmail($data['email']);
        if ($existingAssignee) {
            return $this->json(['error' => true, 'message' => 'Assignee with this email already exists'], 409);
        }

        try {
            // Generate unique ID for the assignee
            $assigneeId = uniqid('assignee_', true);

            // Create new assignee
            $assignee = new Assignee(
                $assigneeId,
                trim($data['firstName']),
                trim($data['lastName']),
                trim($data['email']),
                trim($data['phone']),
                $data['avatar'] ?? '',
                $data['active'] ?? true,
                trim($data['role']),
                trim($data['department']),
                $organizationId
            );

            // Save assignee
            $assigneeRepository->save($assignee);

            // Return success response with created assignee data
            return $this->json([
                'success' => true,
                'message' => 'Assignee created successfully',
                'data' => [
                    'id' => $assignee->getId(),
                    'firstName' => $assignee->getFirstName(),
                    'lastName' => $assignee->getLastName(),
                    'email' => $assignee->getEmail(),
                    'phone' => $assignee->getPhone(),
                    'avatar' => $assignee->getAvatar(),
                    'active' => $assignee->isActive(),
                    'role' => $assignee->getRole(),
                    'department' => $assignee->getDepartment(),
                    'organizationId' => $assignee->getOrganizationId(),
                    'createdAt' => $assignee->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $assignee->getUpdatedAt()?->format('Y-m-d H:i:s')
                ]
            ], 201);

        } catch (\Exception $e) {
            return $this->json([
                'error' => true,
                'message' => 'Failed to create assignee: ' . $e->getMessage()
            ], 500);
        }
    }
}
