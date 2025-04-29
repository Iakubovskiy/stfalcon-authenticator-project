<?php

declare(strict_types=1);

namespace App\Tests;

use App\User\Domain\Validator\UniqueEmailValidator;
use App\User\Register\RegisterDto;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(UniqueEmailValidator::class)]
final class UniqueEmailValidationTest extends KernelTestCase
{
    public function testUniqueEmailOnRegistration(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        /** @var ValidatorInterface $validator */
        $validator = $container->get(ValidatorInterface::class);
        $registerDto = new RegisterDto(
            'test@example.com',
            '123',
            '123',
        );
        $constraintViolationList = $validator->validate($registerDto);
        $this->assertGreaterThan(0, count($constraintViolationList));
        $this->assertSame(
            'not unique email',
            $constraintViolationList->get(0)->getMessage()
        );
    }

    public function testUniqueEmailOnRegistrationSuccess(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        /** @var ValidatorInterface $validator */
        $validator = $container->get(ValidatorInterface::class);
        $registerDto = new RegisterDto(
            'test1@example.com',
            '123',
            '123',
        );
        $constraintViolationList = $validator->validate($registerDto);
        $this->assertLessThan(1, count($constraintViolationList));
    }
}
