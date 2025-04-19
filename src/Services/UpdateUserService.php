<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\UpdateUserDto;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class UpdateUserService
{
    public function __construct(
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager,
        private UserService $userService,
    ) {

    }

    public function updateLastLogin(Uuid $uuid): void
    {
        $user = $this->userService->getUserById($uuid);
        $user->setLastLogin(Carbon::now());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

    }

    public function updateUser(Uuid $id, UpdateUserDto $updateUserDto): void
    {
        $constraintViolationList = $this->validator->validate($updateUserDto);
        if (count($constraintViolationList) > 0) {
            throw new ValidationFailedException($updateUserDto, $constraintViolationList);
        }

        $user = $this->userService->getUserById($id);
        $user->setEmail($updateUserDto->email);
        if ($updateUserDto->password !== null) {
            $hashed_password = $this->userPasswordHasher->hashPassword($user, $updateUserDto->password);
            $user->setPassword($hashed_password);
        }

        if ($updateUserDto->photoPath !== null) {
            $user->setPhotoUrl($updateUserDto->photoPath);
        }

        $this->entityManager->flush();
    }
}
