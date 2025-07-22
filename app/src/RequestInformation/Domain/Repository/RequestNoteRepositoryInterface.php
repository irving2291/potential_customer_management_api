<?php
namespace App\RequestInformation\Domain\Repository;

use App\RequestInformation\Domain\Entity\RequestNote;

interface RequestNoteRepositoryInterface
{
    public function findById(string $id): RequestNote;

    public function save(RequestNote $note): void;

    /** @return RequestNote[] */
    public function findByRequestInformationId(string $requestInformationId): array;
}
