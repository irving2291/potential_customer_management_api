<?php

namespace App\Activation\Application\UseCase;

use App\Activation\Domain\Aggregate\Activation;
use App\Activation\Domain\Repository\ActivationRepositoryInterface;

class CreateActivationUseCase
{
    private ActivationRepositoryInterface $activationRepository;

    public function __construct(ActivationRepositoryInterface $activationRepository)
    {
        $this->activationRepository = $activationRepository;
    }

    public function execute(
        string $organizationId,
        string $title,
        string $description,
        string $type,
        string $priority,
        array $channels,
        string $createdBy,
        ?string $targetAudience = null,
        ?string $scheduledFor = null
    ): array {
        $id = uniqid();
        $scheduledForDate = $scheduledFor ? new \DateTimeImmutable($scheduledFor) : null;

        $activation = new Activation(
            $id,
            $title,
            $description,
            $type,
            $priority,
            $channels,
            $organizationId,
            $createdBy,
            $targetAudience,
            $scheduledForDate
        );

        $this->activationRepository->save($activation);

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