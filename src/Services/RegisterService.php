<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\RegisterDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class RegisterService
{
    public function __construct(
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager,
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
}
