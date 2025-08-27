<?php

namespace App\LandingPage\Infrastructure\Controller;

use App\LandingPage\Domain\Aggregate\LandingPage;
use App\LandingPage\Domain\Repository\LandingPageRepositoryInterface;
use App\LandingPage\Domain\Service\HtmlTemplateService;
use App\RequestInformation\Application\Command\CreateRequestInformationCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use OpenApi\Attributes as OA;

class LandingPageController extends AbstractController
{
    private LandingPageRepositoryInterface $landingPageRepository;
    private HtmlTemplateService $templateService;

    public function __construct(
        LandingPageRepositoryInterface $landingPageRepository,
        HtmlTemplateService $templateService
    ) {
        $this->landingPageRepository = $landingPageRepository;
        $this->templateService = $templateService;
    }
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

        // Get landing pages from repository
        if ($published !== null) {
            $isPublished = filter_var($published, FILTER_VALIDATE_BOOLEAN);
            if ($isPublished) {
                $landingPages = $this->landingPageRepository->findPublishedByOrganizationId($organizationId);
            } else {
                // Get all pages and filter unpublished ones
                $allPages = $this->landingPageRepository->findByOrganizationId($organizationId);
                $landingPages = array_filter($allPages, fn(LandingPage $page) => !$page->isPublished());
            }
        } else {
            $landingPages = $this->landingPageRepository->findByOrganizationId($organizationId);
        }

        // Convert domain objects to array format
        $data = array_map(function (LandingPage $landingPage) {
            return [
                'id' => $landingPage->getId(),
                'title' => $landingPage->getTitle(),
                'slug' => $landingPage->getSlug(),
                'isPublished' => $landingPage->isPublished(),
                'hasContactForm' => $landingPage->hasContactForm(),
                'createdBy' => $landingPage->getCreatedBy(),
                'createdAt' => $landingPage->getCreatedAt()->format('Y-m-d\TH:i:s\Z'),
                'updatedAt' => $landingPage->getUpdatedAt()?->format('Y-m-d\TH:i:s\Z')
            ];
        }, $landingPages);

        return $this->json(['data' => $data]);
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
                    new OA\Property(property: "contactFormConfig", type: "object", nullable: true),
                    new OA\Property(property: "variables", type: "object", nullable: true, description: "Key-value pairs for template variables")
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

        // Validate variables if provided
        $variables = $data['variables'] ?? [];
        if (!is_array($variables)) {
            return $this->json(['error' => true, 'message' => 'Variables must be an object'], 400);
        }

        // Validate variable names
        foreach (array_keys($variables) as $varName) {
            if (!$this->templateService->validateVariableName($varName)) {
                return $this->json(['error' => true, 'message' => "Invalid variable name: $varName"], 400);
            }
        }

        // Create new landing page
        $pageId = uniqid();
        $landingPage = new LandingPage(
            $pageId,
            $data['title'],
            $data['slug'],
            $data['htmlContent'],
            $organizationId,
            'current-user', // Should come from authentication
            $data['hasContactForm'] ?? false,
            $data['contactFormConfig'] ?? null,
            $variables
        );

        // Set published status if provided
        if (isset($data['isPublished']) && $data['isPublished']) {
            $landingPage->publish();
        }

        // Save to repository
        $this->landingPageRepository->save($landingPage);

        // Return created landing page data
        return $this->json([
            'id' => $landingPage->getId(),
            'title' => $landingPage->getTitle(),
            'slug' => $landingPage->getSlug(),
            'htmlContent' => $landingPage->getHtmlContent(),
            'isPublished' => $landingPage->isPublished(),
            'hasContactForm' => $landingPage->hasContactForm(),
            'contactFormConfig' => $landingPage->getContactFormConfig(),
            'variables' => $landingPage->getVariables(),
            'createdBy' => $landingPage->getCreatedBy(),
            'createdAt' => $landingPage->getCreatedAt()->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => $landingPage->getUpdatedAt()?->format('Y-m-d\TH:i:s\Z')
        ], 201);
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
        $landingPage = $this->landingPageRepository->findById($id);
        
        if (!$landingPage) {
            return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
        }

        return $this->json([
            'id' => $landingPage->getId(),
            'title' => $landingPage->getTitle(),
            'slug' => $landingPage->getSlug(),
            'htmlContent' => $landingPage->getHtmlContent(),
            'isPublished' => $landingPage->isPublished(),
            'hasContactForm' => $landingPage->hasContactForm(),
            'contactFormConfig' => $landingPage->getContactFormConfig(),
            'variables' => $landingPage->getVariables(),
            'createdBy' => $landingPage->getCreatedBy(),
            'createdAt' => $landingPage->getCreatedAt()->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => $landingPage->getUpdatedAt()?->format('Y-m-d\TH:i:s\Z')
        ]);
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
        $landingPage = $this->landingPageRepository->findBySlug($slug);
        
        if (!$landingPage) {
            return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
        }

        return $this->json([
            'id' => $landingPage->getId(),
            'title' => $landingPage->getTitle(),
            'slug' => $landingPage->getSlug(),
            'htmlContent' => $landingPage->getProcessedHtmlContent($this->templateService),
            'rawHtmlContent' => $landingPage->getHtmlContent(),
            'hasContactForm' => $landingPage->hasContactForm(),
            'contactFormConfig' => $landingPage->getContactFormConfig(),
            'variables' => $landingPage->getVariables()
        ]);
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
        $landingPage = $this->landingPageRepository->findById($id);
        
        if (!$landingPage) {
            return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        // Validate variables if provided
        if (isset($data['variables'])) {
            if (!is_array($data['variables'])) {
                return $this->json(['error' => true, 'message' => 'Variables must be an object'], 400);
            }
            
            // Validate variable names
            foreach (array_keys($data['variables']) as $varName) {
                if (!$this->templateService->validateVariableName($varName)) {
                    return $this->json(['error' => true, 'message' => "Invalid variable name: $varName"], 400);
                }
            }
        }
        
        // Update content if provided
        if (isset($data['title']) || isset($data['slug']) || isset($data['htmlContent']) ||
            isset($data['hasContactForm']) || isset($data['contactFormConfig']) || isset($data['variables'])) {
            $landingPage->updateContent(
                $data['title'] ?? $landingPage->getTitle(),
                $data['slug'] ?? $landingPage->getSlug(),
                $data['htmlContent'] ?? $landingPage->getHtmlContent(),
                $data['hasContactForm'] ?? $landingPage->hasContactForm(),
                $data['contactFormConfig'] ?? $landingPage->getContactFormConfig(),
                $data['variables'] ?? $landingPage->getVariables()
            );
        }

        // Update published status if provided
        if (isset($data['isPublished'])) {
            if ($data['isPublished'] && !$landingPage->isPublished()) {
                $landingPage->publish();
            } elseif (!$data['isPublished'] && $landingPage->isPublished()) {
                $landingPage->unpublish();
            }
        }

        // Save changes
        $this->landingPageRepository->save($landingPage);

        return $this->json([
            'id' => $landingPage->getId(),
            'title' => $landingPage->getTitle(),
            'slug' => $landingPage->getSlug(),
            'htmlContent' => $landingPage->getHtmlContent(),
            'isPublished' => $landingPage->isPublished(),
            'hasContactForm' => $landingPage->hasContactForm(),
            'contactFormConfig' => $landingPage->getContactFormConfig(),
            'variables' => $landingPage->getVariables(),
            'createdBy' => $landingPage->getCreatedBy(),
            'createdAt' => $landingPage->getCreatedAt()->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => $landingPage->getUpdatedAt()?->format('Y-m-d\TH:i:s\Z')
        ]);
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
        $landingPage = $this->landingPageRepository->findById($id);
        
        if (!$landingPage) {
            return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
        }

        $this->landingPageRepository->delete($id);

        return $this->json(['success' => true, 'message' => 'Landing page deleted successfully']);
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

        // Check if landing page exists and has contact form
        $landingPage = $this->landingPageRepository->findById($id);
        if (!$landingPage) {
            return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
        }

        if (!$landingPage->hasContactForm()) {
            return $this->json(['error' => true, 'message' => 'Landing page does not have a contact form'], 400);
        }

        // Create a request information from the form submission
        try {
            $command = new CreateRequestInformationCommand(
                $data['programInterest'] ?? 'Consulta desde Landing Page',
                'Landing Page: ' . $landingPage->getTitle(),
                $landingPage->getOrganizationId(),
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

    #[Route('/landing-pages/{id}/variables', name: 'get_landing_page_variables', methods: ['GET'])]
    #[OA\Get(
        summary: "Get landing page variables",
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
            new OA\Response(
                response: 200,
                description: "Landing page variables",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "variables", type: "object"),
                        new OA\Property(property: "extractedVariables", type: "array", items: new OA\Items(type: "string")),
                        new OA\Property(property: "missingVariables", type: "array", items: new OA\Items(type: "string"))
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Landing page not found")
        ]
    )]
    public function getLandingPageVariables(string $id): JsonResponse
    {
        $landingPage = $this->landingPageRepository->findById($id);
        
        if (!$landingPage) {
            return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
        }

        $extractedVars = $landingPage->getExtractedVariables($this->templateService);
        $currentVars = $landingPage->getVariables();
        $missingVars = array_diff($extractedVars, array_keys($currentVars));

        return $this->json([
            'variables' => $currentVars,
            'extractedVariables' => $extractedVars,
            'missingVariables' => $missingVars
        ]);
    }

    #[Route('/landing-pages/{id}/variables', name: 'update_landing_page_variables', methods: ['PUT'])]
    #[OA\Put(
        summary: "Update landing page variables",
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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["variables"],
                properties: [
                    new OA\Property(property: "variables", type: "object", description: "Key-value pairs for template variables")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Variables updated successfully"),
            new OA\Response(response: 404, description: "Landing page not found"),
            new OA\Response(response: 400, description: "Invalid variables data")
        ]
    )]
    public function updateLandingPageVariables(string $id, Request $request): JsonResponse
    {
        $landingPage = $this->landingPageRepository->findById($id);
        
        if (!$landingPage) {
            return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['variables']) || !is_array($data['variables'])) {
            return $this->json(['error' => true, 'message' => 'Variables must be provided as an object'], 400);
        }

        // Validate variable names
        foreach (array_keys($data['variables']) as $varName) {
            if (!$this->templateService->validateVariableName($varName)) {
                return $this->json(['error' => true, 'message' => "Invalid variable name: $varName"], 400);
            }
        }

        $landingPage->updateVariables($data['variables']);
        $this->landingPageRepository->save($landingPage);

        return $this->json([
            'success' => true,
            'message' => 'Variables updated successfully',
            'variables' => $landingPage->getVariables()
        ]);
    }

    #[Route('/landing-pages/{id}/variables/{varName}', name: 'add_landing_page_variable', methods: ['POST'])]
    #[OA\Post(
        summary: "Add or update a single landing page variable",
        tags: ['Landing Pages'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Landing page ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "varName",
                description: "Variable name",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["value"],
                properties: [
                    new OA\Property(property: "value", type: "string", description: "Variable value")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Variable added/updated successfully"),
            new OA\Response(response: 404, description: "Landing page not found"),
            new OA\Response(response: 400, description: "Invalid variable data")
        ]
    )]
    public function addLandingPageVariable(string $id, string $varName, Request $request): JsonResponse
    {
        $landingPage = $this->landingPageRepository->findById($id);
        
        if (!$landingPage) {
            return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
        }

        if (!$this->templateService->validateVariableName($varName)) {
            return $this->json(['error' => true, 'message' => "Invalid variable name: $varName"], 400);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['value'])) {
            return $this->json(['error' => true, 'message' => 'Variable value is required'], 400);
        }

        $landingPage->addVariable($varName, (string)$data['value']);
        $this->landingPageRepository->save($landingPage);

        return $this->json([
            'success' => true,
            'message' => 'Variable added/updated successfully',
            'variable' => [$varName => $data['value']]
        ]);
    }

    #[Route('/landing-pages/{id}/variables/{varName}', name: 'delete_landing_page_variable', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Delete a landing page variable",
        tags: ['Landing Pages'],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Landing page ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "varName",
                description: "Variable name",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Variable deleted successfully"),
            new OA\Response(response: 404, description: "Landing page not found")
        ]
    )]
    public function deleteLandingPageVariable(string $id, string $varName): JsonResponse
    {
        $landingPage = $this->landingPageRepository->findById($id);
        
        if (!$landingPage) {
            return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
        }

        $landingPage->removeVariable($varName);
        $this->landingPageRepository->save($landingPage);

        return $this->json([
            'success' => true,
            'message' => 'Variable deleted successfully'
        ]);
    }

    #[Route('/landing-pages/{id}/preview', name: 'preview_landing_page', methods: ['GET'])]
    #[OA\Get(
        summary: "Get landing page preview with processed variables",
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
            new OA\Response(
                response: 200,
                description: "Landing page preview",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "processedHtml", type: "string"),
                        new OA\Property(property: "extractedVariables", type: "array", items: new OA\Items(type: "string")),
                        new OA\Property(property: "missingVariables", type: "array", items: new OA\Items(type: "string"))
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Landing page not found")
        ]
    )]
    public function previewLandingPage(string $id): JsonResponse
    {
        $landingPage = $this->landingPageRepository->findById($id);
        
        if (!$landingPage) {
            return $this->json(['error' => true, 'message' => 'Landing page not found'], 404);
        }

        $preview = $landingPage->getTemplatePreview($this->templateService);

        return $this->json($preview);
    }
}