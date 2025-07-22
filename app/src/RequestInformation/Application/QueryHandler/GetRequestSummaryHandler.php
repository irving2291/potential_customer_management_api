<?php

namespace App\RequestInformation\Application\QueryHandler;

use App\RequestInformation\Application\Query\GetRequestSummaryQuery;
use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetRequestSummaryHandler
{
    public function __construct(private RequestInformationRepositoryInterface $repository) {}

    public function __invoke(GetRequestSummaryQuery $query): array
    {
        // Aquí llamas al método getSummaryByDates en tu repo
        return $this->repository->getSummaryByDates($query->from, $query->to);
    }
}
