<?php

namespace App\Activation\Application\UseCase;

use App\Activation\Domain\Repository\ActivationRepositoryInterface;

class GetActivationUseCase
{
    private ActivationRepositoryInterface $activationRepository;

    public function __construct(ActivationRepositoryInterface $activationRepository)
    {
        $this->activationRepository = $activationRepository;
    }

    public function execute(string $id): ?array
    {
        $activation = $this->activationRepository->findById($id);

        if (!$activation) {
            return null;
        }

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