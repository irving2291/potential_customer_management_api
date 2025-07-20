<?php
namespace App\RequestInformation\Application\QueryHandler;

use App\RequestInformation\Domain\Repository\RequestInformationRepositoryInterface;
use App\RequestInformation\Application\Query\CountRequestsQuery;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CountRequestsHandler
{
    private RequestInformationRepositoryInterface $repository;

    public function __construct(RequestInformationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(CountRequestsQuery $query): int
    {
        return $this->repository->countAll();
    }
}
