<?php

namespace App\Shared\Infrastructure;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelEvents;

class OrganizationHeaderSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $orgId = $request->headers->get('x-org-id');

        if (str_starts_with($request->getPathInfo(), '/requests-information')) {
            if (!$orgId) {
                $response = new JsonResponse(['error' => true, 'message' => 'Organization header (x-org-id) is required'], 400);
                $event->setResponse($response);
            }
        }
    }
}
