<?php

namespace App\PotentialCustomer\Infrastructure\Controller;

use App\PotentialCustomer\Domain\Repository\PotentialCustomerRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class PotentialCustomerController extends AbstractController
{
    #[Route('/accounts', name: 'list_accounts', methods: ['GET'])]
    #[OA\Get(
        summary: "List all accounts (potential customers) by organization",
        tags: ['Accounts'],
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
                name: "type",
                description: "Filter by account type",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", enum: ["person", "company", "all"])
            ),
            new OA\Parameter(
                name: "status",
                description: "Filter by status",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", enum: ["prospect", "client", "inactive", "all"])
            ),
            new OA\Parameter(
                name: "priority",
                description: "Filter by priority",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", enum: ["low", "medium", "high"])
            ),
            new OA\Parameter(
                name: "assignedTo",
                description: "Filter by assigned user",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "search",
                description: "Search in names and email",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of accounts",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string"),
                                    new OA\Property(property: "type", type: "string"),
                                    new OA\Property(property: "status", type: "string"),
                                    new OA\Property(property: "firstName", type: "string", nullable: true),
                                    new OA\Property(property: "lastName", type: "string", nullable: true),
                                    new OA\Property(property: "companyName", type: "string", nullable: true),
                                    new OA\Property(property: "email", type: "string"),
                                    new OA\Property(property: "phone", type: "string"),
                                    new OA\Property(property: "city", type: "string", nullable: true),
                                    new OA\Property(property: "country", type: "string", nullable: true),
                                    new OA\Property(property: "tags", type: "array", items: new OA\Items(type: "string")),
                                    new OA\Property(property: "priority", type: "string"),
                                    new OA\Property(property: "assignedTo", type: "string", nullable: true),
                                    new OA\Property(property: "assignedToName", type: "string", nullable: true),
                                    new OA\Property(property: "totalValue", type: "number", format: "float"),
                                    new OA\Property(property: "potentialValue", type: "number", format: "float"),
                                    new OA\Property(property: "lastContactDate", type: "string", nullable: true),
                                    new OA\Property(property: "requestsCount", type: "integer"),
                                    new OA\Property(property: "quotationsCount", type: "integer"),
                                    new OA\Property(property: "conversationsCount", type: "integer"),
                                    new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                                    new OA\Property(property: "updatedAt", type: "string", format: "date-time", nullable: true),
                                    new OA\Property(property: "convertedAt", type: "string", format: "date-time", nullable: true)
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
    public function listAccounts(Request $request): JsonResponse
    {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = max(1, min(100, (int) $request->query->get('perPage', 20)));
        $type = $request->query->get('type');
        $status = $request->query->get('status');
        $priority = $request->query->get('priority');
        $assignedTo = $request->query->get('assignedTo');
        $search = $request->query->get('search');

        // Mock data compatible with frontend expectations
        $mockAccounts = [
            [
                'id' => '1',
                'type' => 'person',
                'status' => 'client',
                'firstName' => 'Juan',
                'lastName' => 'Pérez',
                'companyName' => null,
                'email' => 'juan.perez@email.com',
                'phone' => '+593 99 123 4567',
                'address' => 'Av. Amazonas 123',
                'city' => 'Quito',
                'country' => 'Ecuador',
                'website' => null,
                'industry' => null,
                'tags' => ['VIP', 'Referido'],
                'priority' => 'high',
                'assignedTo' => 'user1',
                'assignedToName' => 'María García',
                'totalValue' => 15000,
                'potentialValue' => 25000,
                'lastContactDate' => '2024-01-15',
                'requestsCount' => 3,
                'quotationsCount' => 2,
                'conversationsCount' => 5,
                'createdAt' => '2024-01-01T00:00:00Z',
                'updatedAt' => '2024-01-15T00:00:00Z',
                'convertedAt' => '2024-01-10T00:00:00Z'
            ],
            [
                'id' => '2',
                'type' => 'company',
                'status' => 'prospect',
                'firstName' => null,
                'lastName' => null,
                'companyName' => 'TechCorp S.A.',
                'email' => 'contacto@techcorp.com',
                'phone' => '+593 2 234 5678',
                'address' => 'Av. República 456',
                'city' => 'Guayaquil',
                'country' => 'Ecuador',
                'website' => 'https://techcorp.com',
                'industry' => 'Tecnología',
                'tags' => ['Empresa', 'Software'],
                'priority' => 'medium',
                'assignedTo' => 'user2',
                'assignedToName' => 'Carlos López',
                'totalValue' => 0,
                'potentialValue' => 50000,
                'lastContactDate' => '2024-01-20',
                'requestsCount' => 1,
                'quotationsCount' => 1,
                'conversationsCount' => 3,
                'createdAt' => '2024-01-05T00:00:00Z',
                'updatedAt' => '2024-01-20T00:00:00Z',
                'convertedAt' => null
            ],
            [
                'id' => '3',
                'type' => 'person',
                'status' => 'prospect',
                'firstName' => 'Ana',
                'lastName' => 'Rodríguez',
                'companyName' => null,
                'email' => 'ana.rodriguez@email.com',
                'phone' => '+593 98 765 4321',
                'address' => 'Calle 10 de Agosto 789',
                'city' => 'Cuenca',
                'country' => 'Ecuador',
                'website' => null,
                'industry' => null,
                'tags' => ['Nuevo', 'Web'],
                'priority' => 'low',
                'assignedTo' => 'user1',
                'assignedToName' => 'María García',
                'totalValue' => 0,
                'potentialValue' => 8000,
                'lastContactDate' => '2024-01-18',
                'requestsCount' => 1,
                'quotationsCount' => 0,
                'conversationsCount' => 2,
                'createdAt' => '2024-01-10T00:00:00Z',
                'updatedAt' => '2024-01-18T00:00:00Z',
                'convertedAt' => null
            ]
        ];

        // Apply filters
        $filteredAccounts = $mockAccounts;
        if ($type && $type !== 'all') {
            $filteredAccounts = array_filter($filteredAccounts, fn($a) => $a['type'] === $type);
        }
        if ($status && $status !== 'all') {
            $filteredAccounts = array_filter($filteredAccounts, fn($a) => $a['status'] === $status);
        }
        if ($priority) {
            $filteredAccounts = array_filter($filteredAccounts, fn($a) => $a['priority'] === $priority);
        }
        if ($assignedTo) {
            $filteredAccounts = array_filter($filteredAccounts, fn($a) => $a['assignedTo'] === $assignedTo);
        }
        if ($search) {
            $search = strtolower($search);
            $filteredAccounts = array_filter($filteredAccounts, fn($a) => 
                str_contains(strtolower($a['firstName'] ?? ''), $search) ||
                str_contains(strtolower($a['lastName'] ?? ''), $search) ||
                str_contains(strtolower($a['companyName'] ?? ''), $search) ||
                str_contains(strtolower($a['email']), $search)
            );
        }

        $total = count($filteredAccounts);
        $offset = ($page - 1) * $perPage;
        $paginatedAccounts = array_slice($filteredAccounts, $offset, $perPage);

        return $this->json([
            'data' => array_values($paginatedAccounts),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage
        ]);
    }

    #[Route('/accounts/stats', name: 'account_stats', methods: ['GET'])]
    #[OA\Get(
        summary: "Get account statistics",
        tags: ['Accounts'],
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
                description: "Account statistics",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "totalAccounts", type: "integer"),
                        new OA\Property(property: "totalClients", type: "integer"),
                        new OA\Property(property: "totalProspects", type: "integer"),
                        new OA\Property(property: "conversionRate", type: "number", format: "float"),
                        new OA\Property(property: "totalValue", type: "number", format: "float"),
                        new OA\Property(property: "potentialValue", type: "number", format: "float"),
                        new OA\Property(
                            property: "byType",
                            properties: [
                                new OA\Property(property: "person", type: "integer"),
                                new OA\Property(property: "company", type: "integer")
                            ],
                            type: "object"
                        ),
                        new OA\Property(
                            property: "byPriority",
                            properties: [
                                new OA\Property(property: "low", type: "integer"),
                                new OA\Property(property: "medium", type: "integer"),
                                new OA\Property(property: "high", type: "integer")
                            ],
                            type: "object"
                        )
                    ]
                )
            )
        ]
    )]
    public function getAccountStats(Request $request): JsonResponse
    {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        // Mock statistics compatible with frontend expectations
        return $this->json([
            'totalAccounts' => 150,
            'totalClients' => 45,
            'totalProspects' => 105,
            'conversionRate' => 0.30,
            'totalValue' => 450000,
            'potentialValue' => 1200000,
            'byType' => [
                'person' => 90,
                'company' => 60
            ],
            'byPriority' => [
                'low' => 50,
                'medium' => 70,
                'high' => 30
            ],
            'byAssignee' => [
                'user1' => 75,
                'user2' => 45,
                'user3' => 30
            ],
            'recentActivity' => [
                [
                    'id' => '1',
                    'accountId' => '1',
                    'type' => 'status_changed',
                    'description' => 'Convertido de prospecto a cliente',
                    'performedBy' => 'user1',
                    'performedByName' => 'María García',
                    'createdAt' => '2024-01-10T00:00:00Z'
                ],
                [
                    'id' => '2',
                    'accountId' => '2',
                    'type' => 'contact_made',
                    'description' => 'Llamada telefónica realizada',
                    'performedBy' => 'user2',
                    'performedByName' => 'Carlos López',
                    'createdAt' => '2024-01-20T00:00:00Z'
                ]
            ]
        ]);
    }

    #[Route('/accounts/{id}', name: 'get_account', methods: ['GET'])]
    #[OA\Get(
        summary: "Get account by ID",
        tags: ['Accounts'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Account ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Account details"),
            new OA\Response(response: 404, description: "Account not found")
        ]
    )]
    public function getAccount(string $id): JsonResponse
    {
        // Mock data - in real implementation, use PotentialCustomerRepositoryInterface
        if ($id === '1') {
            return $this->json([
                'id' => '1',
                'type' => 'person',
                'status' => 'client',
                'firstName' => 'Juan',
                'lastName' => 'Pérez',
                'companyName' => null,
                'email' => 'juan.perez@email.com',
                'phone' => '+593 99 123 4567',
                'address' => 'Av. Amazonas 123',
                'city' => 'Quito',
                'country' => 'Ecuador',
                'website' => null,
                'industry' => null,
                'tags' => ['VIP', 'Referido'],
                'priority' => 'high',
                'assignedTo' => 'user1',
                'assignedToName' => 'María García',
                'totalValue' => 15000,
                'potentialValue' => 25000,
                'lastContactDate' => '2024-01-15',
                'requestsCount' => 3,
                'quotationsCount' => 2,
                'conversationsCount' => 5,
                'createdAt' => '2024-01-01T00:00:00Z',
                'updatedAt' => '2024-01-15T00:00:00Z',
                'convertedAt' => '2024-01-10T00:00:00Z'
            ]);
        }

        return $this->json(['error' => true, 'message' => 'Account not found'], 404);
    }

    #[Route('/accounts', name: 'create_account', methods: ['POST'])]
    #[OA\Post(
        summary: "Create a new account (potential customer)",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["type", "email", "phone", "priority"],
                properties: [
                    new OA\Property(property: "type", type: "string", enum: ["person", "company"]),
                    new OA\Property(property: "firstName", type: "string", nullable: true),
                    new OA\Property(property: "lastName", type: "string", nullable: true),
                    new OA\Property(property: "companyName", type: "string", nullable: true),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "phone", type: "string"),
                    new OA\Property(property: "city", type: "string", nullable: true),
                    new OA\Property(property: "priority", type: "string", enum: ["low", "medium", "high"]),
                    new OA\Property(property: "assignedTo", type: "string", nullable: true),
                    new OA\Property(property: "assignedToName", type: "string", nullable: true)
                ]
            )
        ),
        tags: ['Accounts'],
        responses: [
            new OA\Response(response: 201, description: "Account created successfully"),
            new OA\Response(response: 400, description: "Invalid request data")
        ]
    )]
    public function createAccount(Request $request): JsonResponse
    {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['type'], $data['email'], $data['phone'], $data['priority'])) {
            return $this->json(['error' => true, 'message' => 'Missing required fields'], 400);
        }

        // Mock creation - in real implementation, create PotentialCustomer
        $accountId = uniqid();
        $mockAccount = [
            'id' => $accountId,
            'type' => $data['type'],
            'status' => 'prospect',
            'firstName' => $data['firstName'] ?? null,
            'lastName' => $data['lastName'] ?? null,
            'companyName' => $data['companyName'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'],
            'city' => $data['city'] ?? null,
            'priority' => $data['priority'],
            'assignedTo' => $data['assignedTo'] ?? null,
            'assignedToName' => $data['assignedToName'] ?? null,
            'totalValue' => 0,
            'potentialValue' => 0,
            'lastContactDate' => null,
            'requestsCount' => 0,
            'quotationsCount' => 0,
            'conversationsCount' => 0,
            'createdAt' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => null,
            'convertedAt' => null
        ];

        return $this->json($mockAccount, 201);
    }

    #[Route('/accounts/{id}', name: 'update_account', methods: ['PUT'])]
    #[OA\Put(
        summary: "Update account",
        tags: ['Accounts'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Account ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Account updated successfully"),
            new OA\Response(response: 404, description: "Account not found")
        ]
    )]
    public function updateAccount(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Mock update
        if ($id === '1') {
            return $this->json([
                'id' => $id,
                'type' => $data['type'] ?? 'person',
                'status' => 'client',
                'firstName' => $data['firstName'] ?? 'Juan',
                'lastName' => $data['lastName'] ?? 'Pérez',
                'companyName' => $data['companyName'] ?? null,
                'email' => $data['email'] ?? 'juan.perez@email.com',
                'phone' => $data['phone'] ?? '+593 99 123 4567',
                'city' => $data['city'] ?? 'Quito',
                'priority' => $data['priority'] ?? 'high',
                'assignedTo' => $data['assignedTo'] ?? 'user1',
                'assignedToName' => $data['assignedToName'] ?? 'María García',
                'totalValue' => 15000,
                'potentialValue' => 25000,
                'lastContactDate' => '2024-01-15',
                'requestsCount' => 3,
                'quotationsCount' => 2,
                'conversationsCount' => 5,
                'createdAt' => '2024-01-01T00:00:00Z',
                'updatedAt' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s\Z'),
                'convertedAt' => '2024-01-10T00:00:00Z'
            ]);
        }

        return $this->json(['error' => true, 'message' => 'Account not found'], 404);
    }

    #[Route('/accounts/{id}', name: 'delete_account', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Delete account",
        tags: ['Accounts'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Account ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Account deleted successfully"),
            new OA\Response(response: 404, description: "Account not found")
        ]
    )]
    public function deleteAccount(string $id): JsonResponse
    {
        // Mock deletion
        if ($id === '1') {
            return $this->json(['success' => true, 'message' => 'Account deleted successfully']);
        }

        return $this->json(['error' => true, 'message' => 'Account not found'], 404);
    }
}