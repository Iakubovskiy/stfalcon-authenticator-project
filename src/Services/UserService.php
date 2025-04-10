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

readonly class UserService
{

    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHarsher,
        private EntityManagerInterface $em,
        private TotpAuthenticatorInterface $totpAuthenticator
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
        $user->setTwoFactorAuthenticationEnabled(false);

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

    public function createSecretForUser(Uuid $userId): User
    {
        $user = $this->getUserById($userId);
        $user->setSecretKey($this->totpAuthenticator->generateSecret());
        $this->em->persist($user);
        $this->em->flush();
        return $user;
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
        dd($isCorrect);
        return $isCorrect;
    }
    //$qrCodeContent = $container->get("scheb_two_factor.security.totp_authenticator")->getQRContent($user);
}
