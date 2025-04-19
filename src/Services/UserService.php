<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager,
        private TotpAuthenticatorInterface $totpAuthenticator,
        private EncryptionService $encryptionService,
        private TranslatorInterface $translator,
    ) {
    }

    public function getUserById(Uuid $uuid): User
    {
        return $this->userRepository->find($uuid) ?? throw new RuntimeException($this->translator->trans('errors.user_not_found'));
    }

    public function disableTwoFactorAuthentication(Uuid $uuid, string $password): bool
    {
        $user = $this->getUserById($uuid);
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
        $user = $this->getUserById($uuid);
        $isValidPassword = $this->userPasswordHasher->isPasswordValid($user, $password);
        if (! $isValidPassword) {
            return false;
        }

        $user->setSecretKey($this->encryptionService->encryptSecret($this->totpAuthenticator->generateSecret()));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return true;
    }

    public function getUserQrCodeData(Uuid $uuid): string
    {
        $user = $this->getUserById($uuid);
        return $this->totpAuthenticator->getQRContent($user);
    }
}
