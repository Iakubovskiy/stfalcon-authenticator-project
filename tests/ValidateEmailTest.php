<?php

declare(strict_types=1);

namespace App\Tests;

use App\DTO\UpdateUserDto;
use App\Services\UpdateUserService;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

final class ValidateEmailTest extends KernelTestCase
{
    public function testEmailValidation(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $updateUserService = $container->get(UpdateUserService::class);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/not a valid email/');

        $updateUserDto = new UpdateUserDto(
            'testtest.com',
            null,
            null,
        );
        $id = Uuid::fromString('0196158b-a5bf-7f06-96be-ec13aa7f6902');
        $this->assertTrue(method_exists($updateUserService, 'updateUser'), 'updateUser method not found');
        $updateUserService->updateUser($id, $updateUserDto);

    }
}
