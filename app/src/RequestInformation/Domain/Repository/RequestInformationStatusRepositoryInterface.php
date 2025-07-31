<?php

namespace App\RequestInformation\Domain\Repository;

use App\RequestInformation\Domain\Entity\RequestInformationStatus;

interface RequestInformationStatusRepositoryInterface
{
    public function findByCode(string $code): ?RequestInformationStatus;
    public function findById(string $id): ?RequestInformationStatus;

    public function findDefault(): ?RequestInformationStatus;

}
