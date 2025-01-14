<?php

namespace App\DataFixtures;

use App\Config\ReservationStatus;
use App\Entity\Reservation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ReservationFixtures extends Fixture implements DependentFixtureInterface
{
    public const FORMULAS = [
        'Solo',
        'Standard',
        'Sur mesure',
    ];

    public const MAX_PRICE = 10000;

    public const AOYOS_PARIS_COORDINATES = [48.860147, 2.346209];

    public const NUMBER_RESERVATIONS = 50;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $totalFormulas = count(self::FORMULAS) - 1;
        $status = ReservationStatus::cases();
        $totalStatus = count($status) - 1;

        for ($i = 0; $i < self::NUMBER_RESERVATIONS; $i++) {
            $reservation = new Reservation();
            $reservation->setLastname($faker->lastName());
            $reservation->setFirstName($faker->firstName());
            $reservation->setCompany($faker->company());
            $reservation->setEmail($faker->email());
            $reservation->setPhone($faker->phoneNumber());
            $reservation->setFormula(self::FORMULAS[rand(0, $totalFormulas)]);
            $reservation->setEventType($faker->sentence(4));
            $reservation->setAddress($faker->address());
            $reservation->setDateStart($faker->dateTimeInInterval('+1 week', '+1 days'));
            $reservation->setDateEnd($faker->dateTimeInInterval('+1 week', '+2 days'));
            $reservation->setAttendees($faker->randomNumber(3, true));
            $reservation->setCommentClient($faker->paragraph());
            $reservation->setPrice($faker->numberBetween(0, self::MAX_PRICE));
            $reservation->setStatus($status[rand(0, $totalStatus)]->name);
            if ($reservation->getStatus() === ReservationStatus::Validated->name) {
                $reservation->setArtist($this->getReference('artist_' . rand(0, (ArtistFixtures::NUMBER_ARTISTS - 1))));
            }
            if ($reservation->getStatus() === ReservationStatus::Waiting->name) {
                $reservation->addMusicalStyle(
                    $this->getReference('musicalstyle_' . rand(0, count(MusicalStyleFixtures::MUSICALSTYLES) - 1))
                );
                $reservation->addMusicalStyle(
                    $this->getReference('musicalstyle_' . rand(0, count(MusicalStyleFixtures::MUSICALSTYLES) - 1))
                );
            }
            $reservation->setLatitude(self::AOYOS_PARIS_COORDINATES[0]);
            $reservation->setLongitude(self::AOYOS_PARIS_COORDINATES[1]);

            $manager->persist($reservation);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            MusicalStyleFixtures::class,
            ArtistFixtures::class
        ];
    }
}
