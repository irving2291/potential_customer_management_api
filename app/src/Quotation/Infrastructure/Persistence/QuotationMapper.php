<?php
namespace App\Quotation\Infrastructure\Persistence;

use App\Quotation\Domain\Aggregate\Quotation;
use App\Quotation\Domain\ValueObject\QuotationDetail;
use App\Quotation\Domain\ValueObject\QuotationStatus;
use App\Quotation\Infrastructure\Persistence\DoctrineQuotationEntity;
use App\RequestInformation\Infrastructure\Persistence\DoctrineRequestInformationEntity;

class QuotationMapper
{
    /**
     * Mapea del aggregate de dominio a la entidad Doctrine (para guardar o actualizar)
     */
    public static function toDoctrine(
        Quotation $quotation,
        DoctrineRequestInformationEntity $requestInfoEntity,
        ?DoctrineQuotationEntity $doctrineEntity = null
    ): DoctrineQuotationEntity
    {
        $entity = $doctrineEntity ?? new DoctrineQuotationEntity();

        $entity->setId($quotation->getId());
        $entity->setRequestInformation($requestInfoEntity);
        // Los detalles se serializan como array (para campo JSON)
        $entity->setDetails(array_map(function (QuotationDetail $detail) {
            return [
                'description' => $detail->description,
                'unitPrice'   => $detail->unitPrice,
                'quantity'    => $detail->quantity,
                'total'       => $detail->total,
            ];
        }, $quotation->getDetails()));

        $entity->setStatus($quotation->getStatus()->value);
        $entity->setCreatedAt($quotation->getCreatedAt());
        $entity->setUpdatedAt($quotation->getUpdatedAt());
        $entity->setDeletedAt($quotation->getDeletedAt());

        return $entity;
    }

    /**
     * Mapea de la entidad Doctrine al aggregate de dominio (para tu app)
     */
    public static function toDomain(DoctrineQuotationEntity $entity): Quotation
    {
        $details = array_map(
            fn ($d) => new QuotationDetail(
                $d['description'] ?? '',
                (float) $d['unitPrice'],
                (int) $d['quantity'],
                (float) $d['total']
            ),
            $entity->getDetails()
        );

        return new Quotation(
            $entity->getId(),
            $entity->getRequestInformation()->getId(),
            $details,
            QuotationStatus::from($entity->getStatus()),
            $entity->getCreatedAt(),
            $entity->getUpdatedAt(),
            $entity->getDeletedAt()
        );
    }
}
