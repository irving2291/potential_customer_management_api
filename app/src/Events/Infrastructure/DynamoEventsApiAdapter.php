<?php
declare(strict_types=1);

namespace App\Events\Infrastructure;

use App\Common\Domain\DomainEvent;
use App\Common\Infrastructure\EventPublisher;
use App\Security\AccessTokenProvider;
use Ramsey\Uuid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class DynamoEventsApiAdapter implements EventPublisher
{
    public function __construct(
        private HttpClientInterface $http,
        private AccessTokenProvider $tokens,
        private string $baseUrl,
        private string $tenantHeader = 'X-Tenant-Id',
        private string $traceHeader = 'X-Trace-Id'
    ) {}

    public function publish(DomainEvent $event): void
    {
        $token = $this->tokens->getToken();

        $headers = [
            'Content-Type'      => 'application/json',
            $this->tenantHeader => (string)$event->tenantId(),
            $this->traceHeader  => Uuid::uuid4()->toString(),
            'Authorization'     => 'Bearer '.$token,
        ];

        $body = [
            'entityId'  => $event->entityId(),
            'eventType' => $event->eventType(),
            'actor'     => ['id' => $event->actorId(), 'username' => $event->actorUsername()],
            'payload'   => $event->payload(),
            'ts'        => $event->occurredOn()->format('c'),
            'source'    => 'symfony',
        ];

        $resp   = $this->http->request('POST', rtrim($this->baseUrl, '/').'/events', [
            'headers' => $headers,
            'json'    => $body,
            'timeout' => 5.0,
        ]);
        $status = $resp->getStatusCode();

        // 200/201 ok; 409 = duplicado (idempotencia) -> ok lógico
        if (!in_array($status, [200, 201, 409], true)) {
            $text = $resp->getContent(false);
            throw new \RuntimeException("Events API error ($status): $text");
        }
    }
}
