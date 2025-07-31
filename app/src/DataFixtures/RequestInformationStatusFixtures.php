<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\RequestInformation\Infrastructure\Persistence\DoctrineRequestInformationStatusEntity;

class RequestInformationStatusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist(new DoctrineRequestInformationStatusEntity(
            'new', 'Nuevo', true, 'a851bb2c-6748-4f4f-a3f9-243889b2d834'
        ));
        $manager->persist(new DoctrineRequestInformationStatusEntity(
            'in_progress', 'En progreso', false, 'a851bb2c-6748-4f4f-a3f9-243889b2d834'
        ));
        $manager->persist(new DoctrineRequestInformationStatusEntity(
            'recontact', 'Recontactar', false, 'a851bb2c-6748-4f4f-a3f9-243889b2d834'
        ));
        $manager->persist(new DoctrineRequestInformationStatusEntity(
            'won', 'Ganado', false, 'a851bb2c-6748-4f4f-a3f9-243889b2d834'
        ));
        $manager->persist(new DoctrineRequestInformationStatusEntity(
            'lost', 'Perdido', false, 'a851bb2c-6748-4f4f-a3f9-243889b2d834'
        ));
        // Agrega más estados si quieres...
        $manager->flush();
    }
}
