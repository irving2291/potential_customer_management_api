<?php

// src/Security/AccessTokenProvider.php
namespace App\Security;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AccessTokenProvider
{
    public function __construct(
        private HttpClientInterface $http,
        private CacheInterface $cache,
        private string $tokenUrl,
        private string $clientId,
        private string $clientSecret,
        private string $audience = 'crm-service',
        private string $scopes = 'events:write events:read'
    ) {}

    public function getToken(): string
    {
        return $this->cache->get('idp_access_token', function (ItemInterface $item) {
            $resp = $this->http->request('POST', $this->tokenUrl, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'audience'      => $this->audience,
                    'scope'         => $this->scopes
                ],
                'timeout' => 5.0
            ]);
            $data = $resp->toArray(false);

            if (empty($data['access_token']) || empty($data['expires_in'])) {
                throw new \RuntimeException('No se pudo obtener access_token');
            }

            // Cachea con margen de seguridad
            $ttl = max(60, ((int)$data['expires_in']) - 60);
            $item->expiresAfter($ttl);

            return $data['access_token'];
        });
    }

    // opcional: para forzar refresh si recibes 401/403
    public function invalidate(): void
    {
        $this->cache->delete('idp_access_token');
    }
}
