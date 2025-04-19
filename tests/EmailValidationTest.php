<?php

declare(strict_types=1);

namespace App\Tests;

use App\DTO\UpdateUserDto;
use App\Repository\UserRepository;
use App\Services\EncryptionService;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;

final class EmailValidationTest extends TestCase
{
    private UserService $userService;

    protected function setUp(): void
    {
        $mockUserRepository = $this->createMock(UserRepository::class);
        $mockPasswordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $mockTotpAuthenticator = $this->createMock(TotpAuthenticatorInterface::class);
        $mockEncryptionService = $this->createMock(EncryptionService::class);
        $mockValidator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $this->userService = new UserService(
            $mockUserRepository,
            $mockPasswordHasher,
            $mockEntityManager,
            $mockTotpAuthenticator,
            $mockEncryptionService,
            $mockValidator,
        );
    }

    public function testNotValidEmailValidation(): void
    {
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(' /is not a valid email/');

        $updateUserDto = new UpdateUserDto(
            'fggfgfv',
            null,
            null,
        );
        $this->userService->updateUser(Uuid::fromString('0196158b-a5bf-7f06-96be-ec13aa7f6902'), $updateUserDto);
    }
}
