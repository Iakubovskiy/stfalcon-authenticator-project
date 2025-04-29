<?php

declare(strict_types=1);

namespace App\Tests;

use App\Controller\ProfileEditController;
use App\Entity\User;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ProfileEditController::class)]
final class ProfileEditTest extends WebTestCase
{
    public function testEditionOfAnotherUserIsProhibited(): void
    {
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        /** @var User $user */
        $user = $userRepository->find(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));

        $client->loginUser($user);
        $client->request(
            Request::METHOD_POST,
            '/edit/0196158b-a5bf-7f06-96be-ec13aa7f6902',
            [
                'email' => 'test@test.com',
                'password' => '',
                'profile_picture' => '',
            ],
        );

        self::assertResponseStatusCodeSame(403);
    }

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
            '/edit/017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3',
            [
                'email' => 'test@test.com',
                'password' => '',
                'profile_picture' => '',
            ],
        );

        self::assertResponseRedirects('/edit/017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3');
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
            '/edit/017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3',
        );

        self::assertResponseIsSuccessful();
        $inputs = $crawler->filter('input[name="email"][value="test@example.com"]');
        $this->assertGreaterThan(0, $inputs);
    }
}
