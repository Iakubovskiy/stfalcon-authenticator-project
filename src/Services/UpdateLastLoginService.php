<?php

declare(strict_types=1);

namespace App\Services;

use App\Repository\UserRepository;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

readonly class UpdateLastLoginService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) {

    }

    public function updateLastLogin(Uuid $uuid): void
    {
        $user = $this->userRepository->getUserById($uuid);
        $user->setLastLogin(CarbonImmutable::now());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

    }
}
