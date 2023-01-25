<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
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
