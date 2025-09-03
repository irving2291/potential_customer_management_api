<?php

namespace App\Activation\Application\UseCase;

use App\Activation\Domain\Repository\ActivationRepositoryInterface;

class DeleteActivationUseCase
{
    private ActivationRepositoryInterface $activationRepository;

    public function __construct(ActivationRepositoryInterface $activationRepository)
    {
        $this->activationRepository = $activationRepository;
    }

    public function execute(string $id): bool
    {
        $activation = $this->activationRepository->findById($id);

        if (!$activation) {
            return false;
        }

        $this->activationRepository->delete($id);
        return true;
    }
}