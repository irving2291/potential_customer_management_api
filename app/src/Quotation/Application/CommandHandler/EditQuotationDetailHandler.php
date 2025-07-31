<?php

namespace App\Quotation\Application\CommandHandler;

use App\Quotation\Application\Command\EditQuotationDetailCommand;
use App\Quotation\Domain\Repository\QuotationRepositoryInterface;
use App\Quotation\Domain\ValueObject\QuotationDetail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EditQuotationDetailHandler
{
    public function __construct(private QuotationRepositoryInterface $repo) {}

    public function __invoke(EditQuotationDetailCommand $cmd): void
    {
        $quotation = $this->repo->findById($cmd->quotationId);
        if (!$quotation) {
            throw new \DomainException('Quotation not found');
        }
        $quotation->editDetail($cmd->index, new QuotationDetail($cmd->description, $cmd->unitPrice, $cmd->quantity, $cmd->total));
        $this->repo->save($quotation);
    }
}
