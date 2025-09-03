<?php

namespace App\Activation\Application\UseCase;

use App\Activation\Domain\Repository\ActivationRepositoryInterface;

class UpdateActivationUseCase
{
    private ActivationRepositoryInterface $activationRepository;

    public function __construct(ActivationRepositoryInterface $activationRepository)
    {
        $this->activationRepository = $activationRepository;
    }

    public function execute(
        string $id,
        string $title,
        string $description,
        string $type,
        string $priority,
        array $channels,
        ?string $targetAudience = null,
        ?string $scheduledFor = null
    ): ?array {
        $activation = $this->activationRepository->findById($id);

        if (!$activation) {
            return null;
        }

        $scheduledForDate = $scheduledFor ? new \DateTimeImmutable($scheduledFor) : null;

        $activation->updateInfo(
            $title,
            $description,
            $type,
            $priority,
            $channels,
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