<?php

namespace App\Activation\Application\UseCase;

use App\Activation\Domain\Repository\ActivationRepositoryInterface;

class ChangeActivationStatusUseCase
{
    private ActivationRepositoryInterface $activationRepository;

    public function __construct(ActivationRepositoryInterface $activationRepository)
    {
        $this->activationRepository = $activationRepository;
    }

    public function execute(string $id, string $status, ?string $scheduledFor = null): ?array
    {
        $activation = $this->activationRepository->findById($id);

        if (!$activation) {
            return null;
        }

        switch ($status) {
            case 'active':
                $activation->activate();
                break;
            case 'scheduled':
                if ($scheduledFor) {
                    $scheduledDate = new \DateTimeImmutable($scheduledFor);
                    $activation->schedule($scheduledDate);
                } else {
                    $activation->pause();
                }
                break;
            case 'completed':
                $activation->complete();
                break;
            case 'cancelled':
                $activation->cancel();
                break;
            default:
                throw new \InvalidArgumentException('Invalid status: ' . $status);
        }

        $this->activationRepository->save($activation);

        return [
            'success' => true,
            'message' => 'Activation status changed successfully',
            'newStatus' => $activation->getStatus()
        ];
    }
}