<?php

declare(strict_types=1);

namespace App\Tests;

use App\DTO\UpdateUserDto;
use App\Kernel;
use App\Services\UserService;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

final class ValidateEmailTest extends KernelTestCase
{
    public function testEmailValidation(): void
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();

        // (3) run some service & test the result
        $userService = $container->get(UserService::class);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/not a valid email/');

        $updateUserDto = new UpdateUserDto(
            'testtest.com',
            null,
            null,
        );
        $id = Uuid::fromString('0196158b-a5bf-7f06-96be-ec13aa7f6902');
        $this->assertTrue(method_exists($userService, 'updateUser'), 'updateUser method not found');
        $userService->updateUser($id, $updateUserDto);

    }
}
