<?php

declare(strict_types=1);

namespace App\Tests;

use App\Repository\UserRepository;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

final class UpdateEndpointTest extends WebTestCase
{
    public function testEditionOfAnotherUserIsProhibited(): void
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
            '/edit/0196158b-a5bf-7f06-96be-ec13aa7f6902',
            [
                'email' => 'test@test.com',
                'password' => '',
                'profile_picture' => '',
            ],
        );

        $this->assertResponseStatusCodeSame(403);
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

        $this->assertResponseRedirects('/edit/017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3');
    }
}
