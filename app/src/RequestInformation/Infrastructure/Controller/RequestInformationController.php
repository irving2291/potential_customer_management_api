<?php
namespace App\RequestInformation\Infrastructure\Controller;

use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use App\RequestInformation\Application\Command\CreateRequestInformationCommand;
use App\RequestInformation\Application\Query\CountRequestsQuery;
use App\RequestInformation\Application\QueryHandler\CountRequestsHandler;
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
        $data = json_decode($request->getContent(), true);

        $command = new CreateRequestInformationCommand(
            $data['programInterest'],
            $data['leadOrigin'],
            $data['firstName'],
            $data['lastName'],
            $data['email'],
            $data['phone'],
            $data['city']
        );
        $commandBus->dispatch($command);

        return $this->json(['status' => 'ok']);
    }

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
        RequestInformationRepositoryInterface $repo
    ): JsonResponse {
        $from = $request->query->get('from');
        $to = $request->query->get('to');

        $fromDate = $from ? \DateTimeImmutable::createFromFormat('Y-m-d', $from) : null;
        $toDate = $to ? \DateTimeImmutable::createFromFormat('Y-m-d', $to) : null;

        // Si solo recibe 'to', toma todo hasta esa fecha. Si solo 'from', todo desde esa fecha hasta ahora.
        $summary = $repo->getSummaryByDates($fromDate, $toDate);

        return $this->json($summary);
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

}
