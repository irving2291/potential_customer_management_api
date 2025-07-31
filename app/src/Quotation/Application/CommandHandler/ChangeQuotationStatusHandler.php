<?php

namespace App\Quotation\Application\CommandHandler;

use App\Quotation\Application\Command\ChangeQuotationStatusCommand;
use App\Quotation\Domain\Repository\QuotationRepositoryInterface;
use App\Quotation\Domain\ValueObject\QuotationStatus;
use DomainException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ChangeQuotationStatusHandler
{
    public function __construct(private QuotationRepositoryInterface $repo) {}

    public function __invoke(ChangeQuotationStatusCommand $command): void
    {
        $quotation = $this->repo->findById($command->quotationId);
        if (!$quotation) {
            throw new DomainException('Quotation not found');
        }

        $quotation->setStatus(QuotationStatus::from($command->newStatus));
        $this->repo->save($quotation);
    }
}
