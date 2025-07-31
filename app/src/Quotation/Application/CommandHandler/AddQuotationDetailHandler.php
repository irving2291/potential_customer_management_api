<?php

namespace App\Quotation\Application\CommandHandler;

use App\Quotation\Application\Command\AddQuotationDetailCommand;
use App\Quotation\Domain\Repository\QuotationRepositoryInterface;
use App\Quotation\Domain\ValueObject\QuotationDetail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AddQuotationDetailHandler
{
    public function __construct(private QuotationRepositoryInterface $repo) {}

    public function __invoke(AddQuotationDetailCommand $cmd): void
    {
        $quotation = $this->repo->findById($cmd->quotationId);
        if (!$quotation) {
            throw new \DomainException('Quotation not found');
        }
        $quotation->addDetail(new QuotationDetail($cmd->description, $cmd->unitPrice, $cmd->quantity, $cmd->total));
        $this->repo->save($quotation);
    }
}
