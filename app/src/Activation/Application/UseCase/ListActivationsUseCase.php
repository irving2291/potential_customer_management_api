<?php

namespace App\Activation\Application\UseCase;

use App\Activation\Domain\Repository\ActivationRepositoryInterface;

class ListActivationsUseCase
{
    private ActivationRepositoryInterface $activationRepository;

    public function __construct(ActivationRepositoryInterface $activationRepository)
    {
        $this->activationRepository = $activationRepository;
    }

    public function execute(
        string $organizationId,
        int $page = 1,
        int $perPage = 20,
        ?string $status = null,
        ?string $type = null,
        ?string $search = null
    ): array {
        $activations = $this->activationRepository->findByOrganizationIdPaginated(
            $organizationId,
            $page,
            $perPage,
            $status,
            $type,
            $search
        );

        $total = count($this->activationRepository->findByOrganizationId($organizationId));

        return [
            'data' => array_map([$this, 'activationToArray'], $activations),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage
        ];
    }

    private function activationToArray($activation): array
    {
        return [
            'id' => $activation->getId(),
            'title' => $activation->getTitle(),
            'description' => $activation->getDescription(),
            'type' => $activation->getType(),
            'status' => $activation->getStatus(),
            'priority' => $activation->getPriority(),
            'channels' => $activation->getChannels(),
            'targetAudience' => $activation->getTargetAudience(),
            'scheduledFor' => $activation->getScheduledFor()?->format('Y-m-d\TH:i:s\Z'),
            'createdBy' => $activation->getCreatedBy(),
            'createdAt' => $activation->getCreatedAt()->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => $activation->getUpdatedAt()?->format('Y-m-d\TH:i:s\Z')
        ];
    }
}