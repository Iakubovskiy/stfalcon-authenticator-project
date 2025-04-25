<?php

declare(strict_types=1);

namespace App\Tests;

use App\DTO\UpdateUserDto;
use App\Services\UpdateUserService;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

final class ValidateEmailTest extends KernelTestCase
{
    public function testEmailValidation(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        /** @var UpdateUserService $updateUserService */
        $updateUserService = $container->get(UpdateUserService::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/not a valid email address/');

        $updateUserDto = new UpdateUserDto(
            'testtest.com',
            null,
            null,
        );
        $id = Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3');
        $updateUserService->updateUser($id, $updateUserDto);

    }
}
