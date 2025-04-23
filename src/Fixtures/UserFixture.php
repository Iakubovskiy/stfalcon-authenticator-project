<?php

declare(strict_types=1);

namespace App\Fixtures;

use App\DTO\RegisterDto;
use App\Services\RegisterService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class UserFixture extends Fixture
{
    public function __construct(
        private readonly RegisterService $registerService,
    ) {

    }

    public function load(ObjectManager $manager): void
    {
        $registerDto = new RegisterDto(
            'test@example.com',
            '123',
        );
        $this->registerService->register($registerDto, Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));
    }
}
