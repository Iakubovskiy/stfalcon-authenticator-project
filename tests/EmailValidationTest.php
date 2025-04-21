<?php

declare(strict_types=1);

namespace App\Tests;

use App\DTO\UpdateUserDto;
use App\Repository\UserRepository;
use App\Services\EncryptionService;
use App\Services\UpdateUserService;
use App\Services\UserService;
use App\Validator\ConstrainUniqueEmail;
use App\Validator\ConstrainUniqueEmailValidator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

final class EmailValidationTest extends TestCase
{
    private UpdateUserService $updateUserService;

    protected function setUp(): void
    {
        $mockUserRepository = $this->createMock(UserRepository::class);
        $mockPasswordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $mockTotpAuthenticator = $this->createMock(TotpAuthenticatorInterface::class);
        $mockEncryptionService = $this->createMock(EncryptionService::class);
        $mockTranslator = $this->createMock(TranslatorInterface::class);
        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $factory = new class($mockUserRepository, $tokenStorageMock, $mockTranslator) implements ConstraintValidatorFactoryInterface {
            public function __construct(
                private UserRepository $userRepository,
                private TokenStorageInterface $tokenStorage,
                private TranslatorInterface $translator,
            ) {
            }

            public function getInstance(Constraint $constraint): ConstraintValidatorInterface
            {
                if ($constraint::class === ConstrainUniqueEmail::class) {
                    return new ConstrainUniqueEmailValidator(
                        $this->userRepository,
                        $this->tokenStorage,
                        $this->translator
                    );
                }

                /** @var class-string<ConstraintValidatorInterface> $class */
                $class = $constraint->validatedBy();
                return new $class();
            }
        };
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->setConstraintValidatorFactory($factory)
            ->getValidator();

        $userService = new UserService(
            $mockUserRepository,
            $mockPasswordHasher,
            $mockEntityManager,
            $mockTotpAuthenticator,
            $mockEncryptionService,
            $mockTranslator,
        );
        $this->updateUserService = new UpdateUserService(
            $validator,
            $mockPasswordHasher,
            $mockEntityManager,
            $userService
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
        $this->updateUserService->updateUser(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'), $updateUserDto);
    }
}
