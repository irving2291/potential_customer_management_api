<?php

namespace App\Quotation\Application\CommandHandler;

use App\Quotation\Application\Command\CreateQuotationCommand;
use App\Quotation\Domain\Aggregate\Quotation;
use App\Quotation\Domain\Repository\QuotationRepositoryInterface;
use App\Quotation\Domain\ValueObject\QuotationDetail;
use App\Quotation\Domain\ValueObject\QuotationStatus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[asMessageHandler]
class CreateQuotationHandler
{
    public function __construct(private QuotationRepositoryInterface $repo) {}

    public function __invoke(CreateQuotationCommand $command): void
    {
        if (
            $this->repo->existsActiveQuotationForRequest(
                $command->requestInformationId,
                [QuotationStatus::CREATING, QuotationStatus::SENT, QuotationStatus::ACCEPTED]
            )
        ) {
            throw new \DomainException('Ya existe una cotización activa para esta petición.');
        }
        $details = array_map(
            fn($d) => new QuotationDetail($d['description'], $d['unitPrice'], $d['quantity'], $d['total']),
            $command->details
        );

        $quotation = new Quotation(
            uuid_create(UUID_TYPE_RANDOM),
            $command->requestInformationId,
            $command->organizationId,
            $details,
            $command->status ? QuotationStatus::from($command->status) : QuotationStatus::CREATING,
            new \DateTimeImmutable()
        );

        $this->repo->save($quotation);
    }
}
