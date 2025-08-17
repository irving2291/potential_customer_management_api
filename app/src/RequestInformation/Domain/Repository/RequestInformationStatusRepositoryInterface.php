<?php

namespace App\RequestInformation\Domain\Repository;

use App\RequestInformation\Domain\Entity\RequestInformationStatus;

interface RequestInformationStatusRepositoryInterface
{
    public function save(RequestInformationStatus $request): RequestInformationStatus;
    public function  findByOrganizationId(string $organizationId);

    public function findByIdAndOrganizationId(string $id, string $orgId): ?\App\RequestInformation\Domain\Entity\RequestInformationStatus;

    public function bulkUpdateSort(string $orgId, array $items): void;


    public function findByCode(string $code): ?RequestInformationStatus;

    public function findById(string $id): ?RequestInformationStatus;

    public function findDefault(): ?RequestInformationStatus;

}
