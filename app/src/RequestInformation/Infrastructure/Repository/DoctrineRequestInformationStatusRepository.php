<?php

namespace App\RequestInformation\Infrastructure\Repository;

use App\RequestInformation\Domain\Entity\RequestInformationStatus;
use App\RequestInformation\Domain\Repository\RequestInformationStatusRepositoryInterface;
use App\RequestInformation\Infrastructure\Persistence\DoctrineRequestInformationStatusEntity;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineRequestInformationStatusRepository implements RequestInformationStatusRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByOrganizationId(string $organizationId): array
    {
        $entity = $this->em->getRepository(DoctrineRequestInformationStatusEntity::class)
            ->findBy(['organizationId' => $organizationId]);
        return $entity;
    }

    public function findByCode(string $code): ?RequestInformationStatus
    {
        $entity = $this->em
            ->getRepository(DoctrineRequestInformationStatusEntity::class)
            ->findOneBy(['code' => $code]);

        return $entity ? $this->mapToDomain($entity) : null;
    }

    public function findById(string $id): ?RequestInformationStatus
    {
        $entity = $this->em
            ->getRepository(DoctrineRequestInformationStatusEntity::class)
            ->find($id);

        return $entity ? $this->mapToDomain($entity) : null;
    }

    private function mapToDomain(DoctrineRequestInformationStatusEntity $entity): RequestInformationStatus
    {
        return new RequestInformationStatus(
            $entity->getId(),
            $entity->getCode(),
            $entity->getName(),
            $entity->isDefault(),
            $entity->getOrganization(),
            $entity->getSort()
        );
    }

    public function findDefault(): ?RequestInformationStatus
    {
        $entity = $this->em
            ->getRepository(DoctrineRequestInformationStatusEntity::class)
            ->findOneBy(['isDefault' => true]);

        return $entity ? $this->mapToDomain($entity) : null;
    }

    public function save(RequestInformationStatus $request): RequestInformationStatus
    {
        $repo = $this->em->getRepository(DoctrineRequestInformationStatusEntity::class);

        if ($request->getId()) {
            /** @var DoctrineRequestInformationStatusEntity|null $entity */
            $entity = $repo->find($request->getId());
            if (!$entity) {
                // Si no existe en DB, creamos uno nuevo con el mismo ID (opcional) o lanzamos excepción
                // throw new \DomainException('RequestInformationStatus not found for update.');
                $entity = new DoctrineRequestInformationStatusEntity(
                    $request->getCode(),
                    $request->getName(),
                    $request->getIsDefault(),
                    $request->getOrganizationId()
                );
            }

            // Sincronizar cambios desde dominio → entidad
            $entity->setCode($request->getCode());
            $entity->setName($request->getName());
            $entity->setIsDefault($request->getIsDefault());
            if (method_exists($entity, 'setSort') && method_exists($request, 'getSort')) {
                $entity->setSort((int) $request->getSort());
            }
        } else {
            // Create
            $entity = new DoctrineRequestInformationStatusEntity(
                $request->getCode(),
                $request->getName(),
                $request->getIsDefault(),
                $request->getOrganizationId()
            );
            if (method_exists($entity, 'setSort') && method_exists($request, 'getSort')) {
                $entity->setSort((int) $request->getSort());
            }
        }

        $this->em->persist($entity);
        $this->em->flush();

        // 👇 Devolver **dominio**, no entidad Doctrine
        return $this->mapToDomain($entity);
    }


    /** buscar por id y organización, devolviendo dominio */
    public function findByIdAndOrganizationId(string $id, string $orgId): ?RequestInformationStatus
    {
        $repo = $this->em->getRepository(DoctrineRequestInformationStatusEntity::class);

        $entity = $repo->findOneBy([
            'id'             => $id,
            'organizationId' => $orgId,
        ]);

        return $entity ? $this->mapToDomain($entity) : null;
    }

    /**
     * actualizar sort en bulk (para drag & drop)
     * $items = [['id' => string, 'sort' => int], ...]
     */
    public function bulkUpdateSort(string $orgId, array $items): void
    {
        if (empty($items)) {
            return;
        }

        $repo = $this->em->getRepository(DoctrineRequestInformationStatusEntity::class);

        foreach ($items as $row) {
            if (!isset($row['id'], $row['sort'])) {
                continue; // o lanza excepción si prefieres ser estricto
            }

            /** @var DoctrineRequestInformationStatusEntity|null $entity */
            $entity = $repo->findOneBy([
                'id'             => (string) $row['id'],
                'organizationId' => $orgId,
            ]);

            if ($entity === null) {
                continue; // o lanza DomainException si prefieres fallar
            }

            // asumimos que la entidad Doctrine tiene setSort(int $sort)
            $entity->setSort((int) $row['sort']);
            $this->em->persist($entity);
        }

        $this->em->flush();
    }
}
