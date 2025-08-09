<?php

namespace App\Common\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use \Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class CommonController extends AbstractController
{
    #[Route('/healthz', name: 'health_check', methods: ['GET'])]
    public function healthCheck(): JsonResponse
    {
        return $this->json(['success' => true], Response::HTTP_OK);
    }

    #[Route('/env-test', name: 'health_check', methods: ['GET'])]
    public function envTest(): JsonResponse
    {
        return $this->json([
            'db' => $_ENV['DATABASE_URL'],
        ], 200);
    }
}
