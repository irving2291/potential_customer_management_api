<?php

namespace App\Shared\Infrastructure;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => 'onKernelException',
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HandlerFailedException && $exception->getPrevious()) {
            $exception = $exception->getPrevious();
        }

        // Solo para rutas de /api (opcional)
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/')) {
            return;
        }

        $statusCode = 500;
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        } elseif ($exception instanceof \DomainException) {
            $statusCode = 409;
        }

        $response = new JsonResponse([
            'error' => true,
            'message' => $exception->getMessage(),
            'type' => (new \ReflectionClass($exception))->getShortName(),
            'trace' => $exception->getTrace()
        ], $statusCode);

        $event->setResponse($response);
    }
}
