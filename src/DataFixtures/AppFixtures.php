<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Configuration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $configuration = new Configuration();
        $configuration->setPlaceName("My Super Place!");
        $configuration->setPlaceAddress("Av. René Monory, 86360 Chasseneuil-du-Poitou");
        $configuration->setLongitude(46.666473);
        $configuration->setLatitude(0.367548);
        $configuration->setNbRows(0);
        $configuration->setNbSeatsPerRow(0);
        $manager->persist($configuration);

        $categoryNames = [
            'Concert', 'Opéra', 'Ciné-concert', 'Danse', 'One-man show',
            'Pop', 'Rock', 'Jazz', 'Classique', 'Rap', 'Hip-hop', 'Insolite'
        ];

        foreach ($categoryNames as $categoryName) {
            $category = new Category;
            $category
                ->setName($categoryName);
            $manager->persist($category);
        }

        $manager->flush();
    }
}
