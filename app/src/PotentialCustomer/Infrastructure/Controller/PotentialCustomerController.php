<?php

namespace App\PotentialCustomer\Infrastructure\Controller;

use App\PotentialCustomer\Domain\Aggregate\PotentialCustomer;
use App\PotentialCustomer\Domain\Entity\Email;
use App\PotentialCustomer\Domain\Repository\PotentialCustomerRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class PotentialCustomerController extends AbstractController
{
    private PotentialCustomerRepositoryInterface $potentialCustomerRepository;

    public function __construct(PotentialCustomerRepositoryInterface $potentialCustomerRepository)
    {
        $this->potentialCustomerRepository = $potentialCustomerRepository;
    }
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
        
        // Build filters array
        $filters = [];
        if ($type = $request->query->get('type')) {
            $filters['type'] = $type;
        }
        if ($status = $request->query->get('status')) {
            $filters['status'] = $status;
        }
        if ($priority = $request->query->get('priority')) {
            $filters['priority'] = $priority;
        }
        if ($assignedTo = $request->query->get('assignedTo')) {
            $filters['assignedTo'] = $assignedTo;
        }
        if ($search = $request->query->get('search')) {
            $filters['search'] = $search;
        }

        // Get accounts from repository
        $accounts = $this->potentialCustomerRepository->findByOrganizationId($organizationId, $filters, $page, $perPage);
        $total = $this->potentialCustomerRepository->countByOrganizationId($organizationId, $filters);

        // Convert domain objects to array format
        $data = array_map(function (PotentialCustomer $customer) {
            return [
                'id' => $customer->getId(),
                'type' => $customer->getType(),
                'status' => $customer->getStatus(),
                'firstName' => $customer->getFirstName(),
                'lastName' => $customer->getLastName(),
                'companyName' => $customer->getCompanyName(),
                'email' => $customer->getPrimaryEmail(),
                'phone' => $customer->getPhone(),
                'address' => $customer->getAddress(),
                'city' => $customer->getCity(),
                'country' => $customer->getCountry(),
                'website' => $customer->getWebsite(),
                'industry' => $customer->getIndustry(),
                'tags' => $customer->getTags(),
                'priority' => $customer->getPriority(),
                'assignedTo' => $customer->getAssignedTo(),
                'assignedToName' => $customer->getAssignedToName(),
                'totalValue' => $customer->getTotalValue(),
                'potentialValue' => $customer->getPotentialValue(),
                'lastContactDate' => $customer->getLastContactDate(),
                'requestsCount' => $customer->getRequestsCount(),
                'quotationsCount' => $customer->getQuotationsCount(),
                'conversationsCount' => $customer->getConversationsCount(),
                'createdAt' => $customer->getCreatedAt()->format('Y-m-d\TH:i:s\Z'),
                'updatedAt' => $customer->getUpdatedAt()?->format('Y-m-d\TH:i:s\Z'),
                'convertedAt' => $customer->getConvertedAt()?->format('Y-m-d\TH:i:s\Z')
            ];
        }, $accounts);

        return $this->json([
            'data' => $data,
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

        // Get statistics from repository
        $stats = $this->potentialCustomerRepository->getStatsByOrganizationId($organizationId);

        // Add mock data for fields not yet implemented in repository
        $stats['byAssignee'] = [
            'user1' => 0,
            'user2' => 0,
            'user3' => 0
        ];
        $stats['recentActivity'] = [];

        return $this->json($stats);
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
        $customer = $this->potentialCustomerRepository->findById($id);
        
        if (!$customer) {
            return $this->json(['error' => true, 'message' => 'Account not found'], 404);
        }

        return $this->json([
            'id' => $customer->getId(),
            'type' => $customer->getType(),
            'status' => $customer->getStatus(),
            'firstName' => $customer->getFirstName(),
            'lastName' => $customer->getLastName(),
            'companyName' => $customer->getCompanyName(),
            'email' => $customer->getPrimaryEmail(),
            'phone' => $customer->getPhone(),
            'address' => $customer->getAddress(),
            'city' => $customer->getCity(),
            'country' => $customer->getCountry(),
            'website' => $customer->getWebsite(),
            'industry' => $customer->getIndustry(),
            'tags' => $customer->getTags(),
            'priority' => $customer->getPriority(),
            'assignedTo' => $customer->getAssignedTo(),
            'assignedToName' => $customer->getAssignedToName(),
            'totalValue' => $customer->getTotalValue(),
            'potentialValue' => $customer->getPotentialValue(),
            'lastContactDate' => $customer->getLastContactDate(),
            'requestsCount' => $customer->getRequestsCount(),
            'quotationsCount' => $customer->getQuotationsCount(),
            'conversationsCount' => $customer->getConversationsCount(),
            'createdAt' => $customer->getCreatedAt()->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => $customer->getUpdatedAt()?->format('Y-m-d\TH:i:s\Z'),
            'convertedAt' => $customer->getConvertedAt()?->format('Y-m-d\TH:i:s\Z')
        ]);
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

        // Create email entity
        $emails = [new Email($data['email'])];

        // Create new potential customer
        $accountId = uniqid();
        $customer = new PotentialCustomer(
            $accountId,
            $data['type'],
            $organizationId,
            $emails,
            $data['phone'],
            $data['priority'],
            $data['firstName'] ?? null,
            $data['lastName'] ?? null,
            $data['companyName'] ?? null,
            $data['city'] ?? null,
            $data['assignedTo'] ?? null,
            $data['assignedToName'] ?? null
        );

        // Save to repository
        $this->potentialCustomerRepository->save($customer);

        return $this->json([
            'id' => $customer->getId(),
            'type' => $customer->getType(),
            'status' => $customer->getStatus(),
            'firstName' => $customer->getFirstName(),
            'lastName' => $customer->getLastName(),
            'companyName' => $customer->getCompanyName(),
            'email' => $customer->getPrimaryEmail(),
            'phone' => $customer->getPhone(),
            'city' => $customer->getCity(),
            'priority' => $customer->getPriority(),
            'assignedTo' => $customer->getAssignedTo(),
            'assignedToName' => $customer->getAssignedToName(),
            'totalValue' => $customer->getTotalValue(),
            'potentialValue' => $customer->getPotentialValue(),
            'lastContactDate' => $customer->getLastContactDate(),
            'requestsCount' => $customer->getRequestsCount(),
            'quotationsCount' => $customer->getQuotationsCount(),
            'conversationsCount' => $customer->getConversationsCount(),
            'createdAt' => $customer->getCreatedAt()->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => $customer->getUpdatedAt()?->format('Y-m-d\TH:i:s\Z'),
            'convertedAt' => $customer->getConvertedAt()?->format('Y-m-d\TH:i:s\Z')
        ], 201);
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
        $customer = $this->potentialCustomerRepository->findById($id);
        
        if (!$customer) {
            return $this->json(['error' => true, 'message' => 'Account not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        // Update customer properties
        if (isset($data['type'])) {
            $customer->setType($data['type']);
        }
        if (isset($data['firstName'])) {
            $customer->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $customer->setLastName($data['lastName']);
        }
        if (isset($data['companyName'])) {
            $customer->setCompanyName($data['companyName']);
        }
        if (isset($data['email'])) {
            $customer->setEmails([new Email($data['email'])]);
        }
        if (isset($data['phone'])) {
            $customer->setPhone($data['phone']);
        }
        if (isset($data['address'])) {
            $customer->setAddress($data['address']);
        }
        if (isset($data['city'])) {
            $customer->setCity($data['city']);
        }
        if (isset($data['country'])) {
            $customer->setCountry($data['country']);
        }
        if (isset($data['website'])) {
            $customer->setWebsite($data['website']);
        }
        if (isset($data['industry'])) {
            $customer->setIndustry($data['industry']);
        }
        if (isset($data['tags'])) {
            $customer->setTags($data['tags']);
        }
        if (isset($data['priority'])) {
            $customer->setPriority($data['priority']);
        }
        if (isset($data['assignedTo'])) {
            $customer->setAssignedTo($data['assignedTo']);
        }
        if (isset($data['assignedToName'])) {
            $customer->setAssignedToName($data['assignedToName']);
        }
        if (isset($data['totalValue'])) {
            $customer->setTotalValue($data['totalValue']);
        }
        if (isset($data['potentialValue'])) {
            $customer->setPotentialValue($data['potentialValue']);
        }
        if (isset($data['lastContactDate'])) {
            $customer->setLastContactDate($data['lastContactDate']);
        }

        // Handle status changes
        if (isset($data['status'])) {
            if ($data['status'] === 'client' && $customer->getStatus() !== 'client') {
                $customer->convertToClient();
            } elseif ($data['status'] === 'inactive') {
                $customer->markAsInactive();
            } elseif ($data['status'] === 'prospect') {
                $customer->reactivate();
            }
        }

        // Save changes
        $this->potentialCustomerRepository->save($customer);

        return $this->json([
            'id' => $customer->getId(),
            'type' => $customer->getType(),
            'status' => $customer->getStatus(),
            'firstName' => $customer->getFirstName(),
            'lastName' => $customer->getLastName(),
            'companyName' => $customer->getCompanyName(),
            'email' => $customer->getPrimaryEmail(),
            'phone' => $customer->getPhone(),
            'address' => $customer->getAddress(),
            'city' => $customer->getCity(),
            'country' => $customer->getCountry(),
            'website' => $customer->getWebsite(),
            'industry' => $customer->getIndustry(),
            'tags' => $customer->getTags(),
            'priority' => $customer->getPriority(),
            'assignedTo' => $customer->getAssignedTo(),
            'assignedToName' => $customer->getAssignedToName(),
            'totalValue' => $customer->getTotalValue(),
            'potentialValue' => $customer->getPotentialValue(),
            'lastContactDate' => $customer->getLastContactDate(),
            'requestsCount' => $customer->getRequestsCount(),
            'quotationsCount' => $customer->getQuotationsCount(),
            'conversationsCount' => $customer->getConversationsCount(),
            'createdAt' => $customer->getCreatedAt()->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => $customer->getUpdatedAt()?->format('Y-m-d\TH:i:s\Z'),
            'convertedAt' => $customer->getConvertedAt()?->format('Y-m-d\TH:i:s\Z')
        ]);
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
        $customer = $this->potentialCustomerRepository->findById($id);
        
        if (!$customer) {
            return $this->json(['error' => true, 'message' => 'Account not found'], 404);
        }

        $this->potentialCustomerRepository->delete($id);

        return $this->json(['success' => true, 'message' => 'Account deleted successfully']);
    }
}