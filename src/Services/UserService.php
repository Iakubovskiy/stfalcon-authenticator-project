<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\RegisterDto;
use App\DTO\UpdateUserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager,
        private TotpAuthenticatorInterface $totpAuthenticator,
        private EncryptionService $encryptionService,
        private ValidatorInterface $validator,
    ) {
    }

    public function register(RegisterDto $registerDto): void
    {
        $constraintViolationList = $this->validator->validate($registerDto);
        if (count($constraintViolationList) > 0) {
            throw new ValidationFailedException($registerDto, $constraintViolationList);
        }

        $user = new User();
        $user->setEmail($registerDto->email);

        $hashPassword = $this->userPasswordHasher->hashPassword($user, $registerDto->password);
        $user->setPassword($hashPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function getUserById(Uuid $uuid): User
    {
        return $this->userRepository->find($uuid) ?? throw new RuntimeException('User not found.');
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

    public function updateLastLogin(Uuid $uuid): void
    {
        $user = $this->getUserById($uuid);
        $user->setLastLogin(\Carbon\Carbon::now());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

    }

    public function updateUser(Uuid $uuid, UpdateUserDto $updateUserDto): void
    {
        $user = $this->getUserById($uuid);
        if($user->getEmail() === $updateUserDto->email)
        {
            $updateUserDto->email = null;
        }
        $constraintViolationList = $this->validator->validate($updateUserDto);

        if (count($constraintViolationList) > 0) {
            throw new ValidationFailedException($updateUserDto, $constraintViolationList);
        }

        $user->setEmail($updateUserDto->email);
        if ($updateUserDto->password !== null && $updateUserDto->password !== '' && $updateUserDto->password !== '0') {
            $hashed_password = $this->userPasswordHasher->hashPassword($user, $updateUserDto->password);
            $user->setPassword($hashed_password);
        }

        if ($updateUserDto->photoUrl !== null && $updateUserDto->photoUrl !== '') {
            $user->setPhotoUrl($updateUserDto->photoUrl);
        }

        $this->entityManager->flush();
    }
}
