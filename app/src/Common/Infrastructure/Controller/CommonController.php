<?php

namespace App\Common\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use \Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class CommonController extends AbstractController
{
    #[Route('/api/v1/health', name: 'health_check', methods: ['GET'])]
    public function healthCheck(): JsonResponse
    {
        return $this->json(['success' => true], 200);
    }

    #[Route('/env-test', name: 'health_check', methods: ['GET'])]
    public function envTest(): JsonResponse
    {
        return $this->json([
            'db' => env('DATABASE_URL'),
        ], 200);
    }
}
