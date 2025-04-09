<?php
declare(strict_types=1);

namespace App\Services;

use App\DTO\RegisterDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

readonly class UserService
{

    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHarsher,
        private EntityManagerInterface $em,
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
}
