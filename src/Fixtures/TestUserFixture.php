<?php

declare(strict_types=1);

namespace App\Fixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class TestUserFixture extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {

    }

    public function load(ObjectManager $manager): void
    {
        $user = new User(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));
        $user->setEmail('test@example.com');
        $user->setPassword($this->hasher->hashPassword($user, '123'));

        $manager->persist($user);
        $manager->flush();
    }
}
