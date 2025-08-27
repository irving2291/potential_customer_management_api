<?php

namespace App\LandingPage\Infrastructure\Controller;

use App\RequestInformation\Application\Command\CreateRequestInformationCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use OpenApi\Attributes as OA;

class LandingPageController extends AbstractController
{
    #[Route('/landing-pages', name: 'list_landing_pages', methods: ['GET'])]
    #[OA\Get(
        summary: "List all landing pages by organization",
        tags: ['Landing Pages'],
        parameters: [
            new OA\Parameter(
                name: "X-Org-Id",
                description: "Organization ID",
                in: "header",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "published",
                description: "Filter by published status",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "boolean")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of landing pages",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string"),
                                    new OA\Property(property: "title", type: "string"),
                                    new OA\Property(property: "slug", type: "string"),
                                    new OA\Property(property: "isPublished", type: "boolean"),
                                    new OA\Property(property: "hasContactForm", type: "boolean"),
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
    public function listLandingPages(Request $request): JsonResponse
    {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $published = $request->query->get('published');

        // Mock data for now
        $mockPages = [
            [
                'id' => '1',
                'title' => 'Página de Contacto Principal',
                'slug' => 'contacto-principal',
                'htmlContent' => '<div class="container mx-auto px-4 py-8"><h1>Contáctanos</h1><p>Estamos aquí para ayudarte</p></div>',
                'isPublished' => true,
                'hasContactForm' => true,
                'contactFormConfig' => [
                    'title' => 'Formulario de Contacto',
                    'description' => 'Déjanos tus datos y te contactaremos pronto',
                    'submitButtonText' => 'Enviar Mensaje',
                    'successMessage' => '¡Gracias! Hemos recibido tu mensaje y te contactaremos pronto.',
                    'fields' => [
                        ['id' => 'firstName', 'name' => 'firstName', 'label' => 'Nombre', 'type' => 'text', 'required' => true],
                        ['id' => 'lastName', 'name' => 'lastName', 'label' => 'Apellido', 'type' => 'text', 'required' => true],
                        ['id' => 'email', 'name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                        ['id' => 'phone', 'name' => 'phone', 'label' => 'Teléfono', 'type' => 'phone', 'required' => false],
                        ['id' => 'message', 'name' => 'message', 'label' => 'Mensaje', 'type' => 'textarea', 'required' => true]
                    ]
                ],
                'createdBy' => 'user-1',
                'createdAt' => '2024-01-01T00:00:00Z',
                'updatedAt' => '2024-01-15T00:00:00Z'
            ],
            [
                'id' => '2',
                'title' => 'Landing Page Promocional',
                'slug' => 'promocion-especial',
                'htmlContent' => '<div class="hero"><h1>Oferta Especial</h1><p>50% de descuento</p></div>',
                'isPublished' => false,
                'hasContactForm' => false,
                'contactFormConfig' => null,
                'createdBy' => 'user-2',
                'createdAt' => '2024-01-10T00:00:00Z',
                'updatedAt' => null
            ]
        ];

        // Apply filters
        $filteredPages = $mockPages;
        if ($published !== null) {
            $isPublished = filter_var($published, FILTER_VALIDATE_BOOLEAN);
            $filteredPages = array_filter($filteredPages, fn($p) => $p['isPublished'] === $isPublished);
        }

        return $this->json(['data' => array_values($filteredPages)]);
    }

    #[Route('/landing-pages', name: 'create_landing_page', methods: ['POST'])]
    #[OA\Post(
        summary: "Create a new landing page",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "slug", "htmlContent"],
                properties: [
                    new OA\Property(property: "title", type: "string"),
                    new OA\Property(property: "slug", type: "string"),
                    new OA\Property(property: "htmlContent", type: "string"),
                    new OA\Property(property: "isPublished", type: "boolean", default: false),
                    new OA\Property(property: "hasContactForm", type: "boolean", default: false),
                    new OA\Property(property: "contactFormConfig", type: "object", nullable: true)
                ]
            )
        ),
        tags: ['Landing Pages'],
        responses: [
            new OA\Response(response: 201, description: "Landing page created successfully"),
            new OA\Response(response: 400, description: "Invalid request data")
        ]
    )]
    public function createLandingPage(Request $request): JsonResponse
    {
        $organizationId = $request->headers->get('X-Org-Id');
        if (!$organizationId) {
            return $this->json(['error' => true, 'message' => 'Organization header missing'], 400);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['title'], $data['slug'], $data['htmlContent'])) {
            return $this->json(['error' => true, 'message' => 'Missing required fields'], 400);
        }

        // Mock creation
        $pageId = uniqid();
        $mockPage = [
            'id' => $pageId,
            'title' => $data['title'],
            'slug' => $data['slug'],
            'htmlContent' => $data['htmlContent'],
            'isPublished' => $data['isPublished'] ?? false,
            'hasContactForm' => $data['hasContactForm'] ?? false,
            'contactFormConfig' => $data['contactFormConfig'] ?? null,
            'createdBy' => 'current-user', // Should come from authentication
            'createdAt' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => null
        ];

        return $this->json($mockPage, 201);
    }

    #[Route('/landing-pages/{id}', name: 'get_landing_page', methods: ['GET'])]
    #[OA\Get(
        summary: "Get landing page by ID",
        tags: ['Landing Pages'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Landing page ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Landing page details"),
            new OA\Response(response: 404, description: "Landing page not found")
        ]
    )]
    public function getLandingPage(string $id): JsonResponse
    {
        // Mock data
        if ($id === '1') {
            return $this->json([
                'id' => '1',
                'title' => 'Página de Contacto Principal',
                'slug' => 'contacto-principal',
                'htmlContent' => '<div class="container mx-auto px-4 py-8"><h1>Contáctanos</h1><p>Estamos aquí para ayudarte</p></div>',
                'isPublished' => true,
                'hasContactForm' => true,
                'contactFormConfig' => [
                    'title' => 'Formulario de Contacto',
                    'description' => 'Déjanos tus datos y te contactaremos pronto',
                    'submitButtonText' => 'Enviar Mensaje',
                    'successMessage' => '¡Gracias! Hemos recibido tu mensaje y te contactaremos pronto.',
                    'fields' => [
                        ['id' => 'firstName', 'name' => 'firstName', 'label' => 'Nombre', 'type' => 'text', 'required' => true],
                        ['id' => 'lastName', 'name' => 'lastName', 'label' => 'Apellido', 'type' => 'text', 'required' => true],
                        ['id' => 'email', 'name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                        ['id' => 'phone', 'name' => 'phone', 'label' => 'Teléfono', 'type' => 'phone', 'required' => false],
                        ['id' => 'message', 'name' => 'message', 'label' => 'Mensaje', 'type' => 'textarea', 'required' => true]
                    ]
                ],
                'createdBy' => 'user-1',
                'createdAt' => '2024-01-01T00:00:00Z',
                'updatedAt' => '2024-01-15T00:00:00Z'
            ]);
        }

        return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
    }

    #[Route('/landing-pages/slug/{slug}', name: 'get_landing_page_by_slug', methods: ['GET'])]
    #[OA\Get(
        summary: "Get landing page by slug (for public access)",
        tags: ['Landing Pages'],
        parameters: [
            new OA\Parameter(
                name: "slug",
                description: "Landing page slug",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Landing page details"),
            new OA\Response(response: 404, description: "Landing page not found")
        ]
    )]
    public function getLandingPageBySlug(string $slug): JsonResponse
    {
        // Mock data - only return published pages
        if ($slug === 'contacto-principal') {
            return $this->json([
                'id' => '1',
                'title' => 'Página de Contacto Principal',
                'slug' => 'contacto-principal',
                'htmlContent' => '<div class="container mx-auto px-4 py-8"><h1>Contáctanos</h1><p>Estamos aquí para ayudarte</p></div>',
                'hasContactForm' => true,
                'contactFormConfig' => [
                    'title' => 'Formulario de Contacto',
                    'description' => 'Déjanos tus datos y te contactaremos pronto',
                    'submitButtonText' => 'Enviar Mensaje',
                    'successMessage' => '¡Gracias! Hemos recibido tu mensaje y te contactaremos pronto.',
                    'fields' => [
                        ['id' => 'firstName', 'name' => 'firstName', 'label' => 'Nombre', 'type' => 'text', 'required' => true],
                        ['id' => 'lastName', 'name' => 'lastName', 'label' => 'Apellido', 'type' => 'text', 'required' => true],
                        ['id' => 'email', 'name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                        ['id' => 'phone', 'name' => 'phone', 'label' => 'Teléfono', 'type' => 'phone', 'required' => false],
                        ['id' => 'message', 'name' => 'message', 'label' => 'Mensaje', 'type' => 'textarea', 'required' => true]
                    ]
                ]
            ]);
        }

        return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
    }

    #[Route('/landing-pages/{id}', name: 'update_landing_page', methods: ['PUT'])]
    #[OA\Put(
        summary: "Update landing page",
        tags: ['Landing Pages'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Landing page ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Landing page updated successfully"),
            new OA\Response(response: 404, description: "Landing page not found")
        ]
    )]
    public function updateLandingPage(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Mock update
        if ($id === '1') {
            return $this->json([
                'id' => $id,
                'title' => $data['title'] ?? 'Página de Contacto Principal',
                'slug' => $data['slug'] ?? 'contacto-principal',
                'htmlContent' => $data['htmlContent'] ?? '<div class="container mx-auto px-4 py-8"><h1>Contáctanos</h1><p>Estamos aquí para ayudarte</p></div>',
                'isPublished' => $data['isPublished'] ?? true,
                'hasContactForm' => $data['hasContactForm'] ?? true,
                'contactFormConfig' => $data['contactFormConfig'] ?? [
                    'title' => 'Formulario de Contacto',
                    'description' => 'Déjanos tus datos y te contactaremos pronto',
                    'submitButtonText' => 'Enviar Mensaje',
                    'successMessage' => '¡Gracias! Hemos recibido tu mensaje y te contactaremos pronto.'
                ],
                'createdBy' => 'user-1',
                'createdAt' => '2024-01-01T00:00:00Z',
                'updatedAt' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s\Z')
            ]);
        }

        return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
    }

    #[Route('/landing-pages/{id}', name: 'delete_landing_page', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Delete landing page",
        tags: ['Landing Pages'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Landing page ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Landing page deleted successfully"),
            new OA\Response(response: 404, description: "Landing page not found")
        ]
    )]
    public function deleteLandingPage(string $id): JsonResponse
    {
        // Mock deletion
        if ($id === '1') {
            return $this->json(['success' => true, 'message' => 'Landing page deleted successfully']);
        }

        return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
    }

    #[Route('/landing-pages/{id}/submit', name: 'submit_landing_page_form', methods: ['POST'])]
    #[OA\Post(
        summary: "Submit landing page contact form",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["firstName", "lastName", "email", "message"],
                properties: [
                    new OA\Property(property: "firstName", type: "string"),
                    new OA\Property(property: "lastName", type: "string"),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "phone", type: "string", nullable: true),
                    new OA\Property(property: "message", type: "string"),
                    new OA\Property(property: "programInterest", type: "string", default: "Consulta desde Landing Page"),
                    new OA\Property(property: "city", type: "string", default: "No especificada")
                ]
            )
        ),
        tags: ['Landing Pages'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Landing page ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Form submitted successfully"),
            new OA\Response(response: 404, description: "Landing page not found"),
            new OA\Response(response: 400, description: "Invalid form data")
        ]
    )]
    public function submitLandingPageForm(
        string $id,
        Request $request,
        MessageBusInterface $commandBus
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['firstName'], $data['lastName'], $data['email'], $data['message'])) {
            return $this->json(['error' => true, 'message' => 'Missing required fields'], 400);
        }

        // Mock landing page check
        if ($id !== '1') {
            return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
        }

        // Create a request information from the form submission
        try {
            $command = new CreateRequestInformationCommand(
                $data['programInterest'] ?? 'Consulta desde Landing Page',
                'Landing Page: ' . $id,
                'default-org', // Should be determined from the landing page
                $data['firstName'],
                $data['lastName'],
                $data['email'],
                $data['phone'] ?? '',
                $data['city'] ?? 'No especificada'
            );
            
            $commandBus->dispatch($command);

            return $this->json([
                'success' => true,
                'message' => '¡Gracias! Hemos recibido tu mensaje y te contactaremos pronto.'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => true,
                'message' => 'Error processing form submission'
            ], 500);
        }
    }
}