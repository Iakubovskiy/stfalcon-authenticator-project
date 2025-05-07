<?php

declare(strict_types=1);

namespace App\User\Email\Test;

use App\User\Profile\UseCases\Edit\ProfileEditDto;
use App\User\Profile\UseCases\Edit\ProfileEditService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ProfileEditService::class)]
final class ValidateEmailTest extends KernelTestCase
{
    public function testEmailValidation(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        /** @var ProfileEditService $updateUserService */
        $updateUserService = $container->get(ProfileEditService::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/not a valid email address/');

        $profileEditDto = new ProfileEditDto(
            'testtest.com',
            null,
            null,
        );
        $id = Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3');
        $updateUserService->updateUser($id, $profileEditDto);

    }
}
