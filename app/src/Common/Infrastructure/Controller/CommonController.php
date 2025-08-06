<?php

namespace App\Common\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use \Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CommonController extends AbstractController
{
    #[Route('/api/v1/health', name: 'health_check', methods: ['GET'])]
    public function healthCheck(): JsonResponse
    {
        return $this->json(['success' => true], 200);
    }

    #[Route('/internal/run-migrations', name: 'run_migrations')]
    public function runMigrations(): JsonResponse
    {
        $output = shell_exec('php bin/console doctrine:migrations:migrate --no-interaction');
        return $this->json(['success' => true, 'output' => $output], 200);
    }
}
