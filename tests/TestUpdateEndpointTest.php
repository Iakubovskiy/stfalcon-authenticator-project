<?php

declare(strict_types=1);

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

final class TestUpdateEndpointTest extends WebTestCase
{
    public function testEmailUpdate(): void
    {
        $client = static::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $this->assertTrue(method_exists($userRepository, 'find'), 'find method not found');
        $user = $userRepository->find(Uuid::fromString('0196158b-a5bf-7f06-96be-ec13aa7f6902'));
        $client->loginUser($user);
        $client->request(
            \Symfony\Component\HttpFoundation\Request::METHOD_POST,
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
