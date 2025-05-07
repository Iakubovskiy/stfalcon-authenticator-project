<?php

declare(strict_types=1);

namespace App\User\Profile\UseCases\Edit\Test;

use App\User\Profile\UseCases\Edit\ProfileEditController;
use App\User\Support\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ProfileEditController::class)]
final class ProfileEditTest extends WebTestCase
{
    public function testEmailUpdate(): void
    {
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->find(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));
        if ($user === null) {
            throw new RuntimeException('No user found');
        }

        $client->loginUser($user);
        $client->request(
            Request::METHOD_POST,
            '/edit',
            [
                'email' => 'test@test.com',
                'password' => '',
                'profile_picture' => '',
            ],
        );

        self::assertResponseRedirects('/edit');
    }

    public function testEditPage(): void
    {
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->find(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));
        if ($user === null) {
            throw new RuntimeException('No user found');
        }

        $client->loginUser($user);
        $crawler = $client->request(
            Request::METHOD_GET,
            '/edit',
        );

        self::assertResponseIsSuccessful();
        $inputs = $crawler->filter('input[name="email"][value="test@example.com"]');
        $this->assertGreaterThan(0, $inputs);
    }
}
