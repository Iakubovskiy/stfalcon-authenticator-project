<?php

declare(strict_types=1);

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

final class UpdateEndpointTest extends WebTestCase
{
    public function testEditionOfAnotherUserIsProhibited()
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->find(Uuid::fromString('019633c1-80b9-760b-bcca-4795a3d541e3'));
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
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->find(Uuid::fromString('0196158b-a5bf-7f06-96be-ec13aa7f6902'));
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

        $this->assertResponseRedirects('/edit/0196158b-a5bf-7f06-96be-ec13aa7f6902');
    }
}
