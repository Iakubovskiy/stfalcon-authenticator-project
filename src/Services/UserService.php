<?php
declare(strict_types=1);

namespace App\Services;

use App\DTO\RegisterDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use DateTime;

readonly class UserService
{

    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHarsher,
        private EntityManagerInterface $em,
        private TotpAuthenticatorInterface $totpAuthenticator,
    )
    {}

    public function register(RegisterDto $registerDto): User {
        $existingUser = $this->userRepository->findOneBy(["email"=> $registerDto->email]);
        if($existingUser) {
            throw new ConflictHttpException('Користувач з таким email вже існує.');
        }

        $user = new User();
        $user->setEmail($registerDto->email);
        $hashed_password = $this->passwordHarsher->hashPassword($user, $registerDto->password);
        $user->setPassword($hashed_password);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function getAllUsers(): array
    {
       return $this->userRepository->findAll();
    }

    public function getUserById(Uuid $userId): User
    {
        return $this->userRepository->find($userId);
    }

    public function getUserSecretKey(Uuid $userId): string
    {
        $user = $this->getUserById($userId);
        return $user->getSecretKey();
    }

    public function verifyTotp(Uuid $userId, string $totp): bool
    {
        $user = $this->getUserById($userId);
        $isCorrect = $this->totpAuthenticator->checkCode($user, $totp);
        return $isCorrect;
    }

    public function disableTwoFactorAuthentication(Uuid $userId, string $password): bool
    {
        $user = $this->getUserById($userId);
        $isValidPassword = $this->passwordHarsher->isPasswordValid($user, $password);
        if(!$isValidPassword) {
            return false;
        }
        $user->setSecretKey(null);
        $this->em->persist($user);
        $this->em->flush();
        return true;
    }
    public function enableTwoFactorAuthentication(Uuid $userId, string $password): bool
    {
        $user = $this->getUserById($userId);
        $isValidPassword = $this->passwordHarsher->isPasswordValid($user, $password);
        if(!$isValidPassword) {
            return false;
        }
        $user->setSecretKey($user->encryptSecret($this->totpAuthenticator->generateSecret()));
        $this->em->persist($user);
        $this->em->flush();
        return true;
    }

    public function getUserQrCodeData(Uuid $userId): string
    {
        $user = $this->getUserById($userId);
        return $this->totpAuthenticator->getQRContent($user);
    }

    public function updateLastLogin(Uuid $userId): User
    {
        $user = $this->getUserById($userId);
        $user->setLastLogin(new DateTime());
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
