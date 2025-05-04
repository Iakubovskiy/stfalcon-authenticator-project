<?php

declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin;

use App\User\Support\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

readonly class TwoFactorService
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager,
        private TotpAuthenticatorInterface $totpAuthenticator,
        private UserRepository $userRepository,
    ) {
    }

    public function disableTwoFactorAuthentication(Uuid $uuid, string $password): bool
    {
        $user = $this->userRepository->getUserById($uuid);
        $isValidPassword = $this->userPasswordHasher->isPasswordValid($user, $password);
        if (! $isValidPassword) {
            return false;
        }

        $user->setSecretKey(null);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return true;
    }

    public function enableTwoFactorAuthentication(Uuid $uuid, string $password): bool
    {
        $user = $this->userRepository->getUserById($uuid);
        $isValidPassword = $this->userPasswordHasher->isPasswordValid($user, $password);
        if (! $isValidPassword) {
            return false;
        }

        $user->setSecretKey($this->totpAuthenticator->generateSecret());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return true;
    }

    public function getUserQrCodeData(Uuid $uuid): string
    {
        $user = $this->userRepository->getUserById($uuid);
        //        dd($this->totpAuthenticator->getQRContent($user));
        return $this->totpAuthenticator->getQRContent($user);
    }
}
