<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Artist;
use Faker\Factory;

class ArtistFixtures extends Fixture implements DependentFixtureInterface
{
    public const NUMBER_ARTIST = 5;

    public function load(ObjectManager $manager): void
    {
        $totalMusicalStyles = count(MusicalStyleFixtures::MUSICALSTYLES) - 1;

        $faker = Factory::create();

        for ($i = 0; $i <= self::NUMBER_ARTIST; $i++) {
            $artist = new Artist();

            $artist->setFirstname($faker->firstName());
            $artist->setLastname($faker->lastName());
            $artist->setBirthdate($faker->dateTime());
            $artist->setPhone($faker->phoneNumber());
            $artist->setEmail($faker->email());
            $artist->setAddress($faker->address());
            $artist->setArtistName($faker->word());
            $artist->setEquipment($faker->words(3, true));
            $artist->setMessage($faker->sentence());
            $artist->addMusicalStyle(
                $this->getReference('musicalstyle_' . rand(0, $totalMusicalStyles))
            );


            $manager->persist($artist);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            MusicalStyleFixtures::class,
        ];
    }
}
