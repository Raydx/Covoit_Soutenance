<?php

namespace App\DataFixtures;

use App\Entity\User;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture implements FixtureGroupInterface
{

    private $userPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User;
        $user->setName("Pedro");
        $user->setSurname("Morales");
        $user->setEmail("emailbidon@free.fr");
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "mdp"));
        $manager->persist($user);

        $user = new User;
        $user->setName("Hugo");
        $user->setSurname("Tiros");
        $user->setEmail("nonoui@free.fr");
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "pwd"));
        $manager->persist($user);

        $manager->flush();


    }

    public static function getGroups():array
    {
        return ['group3'];
    }
}
