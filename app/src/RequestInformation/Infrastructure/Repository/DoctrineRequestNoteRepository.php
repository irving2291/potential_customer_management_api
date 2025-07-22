<?php
namespace App\RequestInformation\Infrastructure\Repository;

use App\RequestInformation\Domain\Entity\RequestNote;
use App\RequestInformation\Domain\Repository\RequestNoteRepositoryInterface;
use App\RequestInformation\Infrastructure\Persistence\DoctrineRequestInformationEntity;
use App\RequestInformation\Infrastructure\Persistence\DoctrineRequestNoteEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class DoctrineRequestNoteRepository implements RequestNoteRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(RequestNote $note): void
    {
        $repo = $this->em->getRepository(DoctrineRequestNoteEntity::class);
        $entity = $repo->find($note->id);

        if (!$entity) {
            $requestInfoEntity = $this->em
                ->getRepository(DoctrineRequestInformationEntity::class)
                ->find($note->requestInformationId);

            $entity = new DoctrineRequestNoteEntity(
                $note->id,
                $requestInfoEntity,
                $note->text,
                $note->createdBy,
                $note->createdAt,
            );
        } else {
            // Solo actualizar campos (soft delete/update)
            $entity->setText($note->text);
            $entity->setUpdatedAt($note->updatedAt);
            $entity->setDeletedAt($note->deletedAt);
        }


        $this->em->persist($entity);
        $this->em->flush();
    }

    public function findByRequestInformationId(string $requestInformationId): array
    {
        $repo = $this->em->getRepository(DoctrineRequestNoteEntity::class);
        $entities = $repo
            ->findBy(['requestInformation' => $requestInformationId, 'deletedAt' => null], ['createdAt' => 'DESC']);

        // Mapear entidades Doctrine a objetos de dominio
        return array_map(function(DoctrineRequestNoteEntity $e) {
            return new RequestNote(
                $e->getId(),
                $e->getRequestInformation()->getId(),
                $e->getText(),
                $e->getCreatedBy(),
                $e->getCreatedAt(),
                $e->getUpdatedAt(),
                $e->getDeletedAt()
            );
        }, $entities);
    }

    public function findById(string $id): RequestNote
    {
        $repo = $this->em->getRepository(DoctrineRequestNoteEntity::class);
        $entity = $repo->find($id);
        if (!$entity) {
            throw new EntityNotFoundException();
        }
        return new RequestNote(
            $entity->getId(),
            $entity->getRequestInformation()->getId(),
            $entity->getText(),
            $entity->getCreatedBy(),
            $entity->getCreatedAt()
        );
    }
}
