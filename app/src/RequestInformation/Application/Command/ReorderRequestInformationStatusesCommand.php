<?php

namespace App\RequestInformation\Application\Command;

final class ReorderRequestInformationStatusesCommand
{
    /**
     * @param array<int, array{id: string, sort: int}> $items
     */
    public function __construct(
        private readonly string $organizationId,
        private readonly array $items
    ) {}

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    /**
     * @return array<int, array{id: string, sort: int}>
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
