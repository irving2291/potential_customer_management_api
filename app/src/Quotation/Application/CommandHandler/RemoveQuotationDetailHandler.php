<?php

namespace App\Quotation\Application\CommandHandler;

use App\Quotation\Application\Command\RemoveQuotationDetailCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Quotation\Domain\Repository\QuotationRepositoryInterface;

#[AsMessageHandler]
class RemoveQuotationDetailHandler
{
    public function __construct(private QuotationRepositoryInterface $repo) {}

    public function __invoke(RemoveQuotationDetailCommand $cmd): void
    {
        $quotation = $this->repo->findById($cmd->quotationId);
        if (!$quotation) {
            throw new \DomainException('Quotation not found');
        }
        $quotation->removeDetail($cmd->index);
        $this->repo->save($quotation);
    }
}
