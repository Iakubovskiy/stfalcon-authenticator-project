<?php

declare(strict_types=1);

namespace App\User\Profile\UseCases\Get\Test;

use App\User\Profile\UseCases\Get\ProfilePageController;
use App\User\Support\UserRepository;
use App\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ProfilePageController::class)]
final class ProfileControllerTest extends WebTestCase
{
    public function testMainPage(): void
    {
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        /** @var User $user */
        $user = $userRepository->find(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));

        $client->loginUser($user);
        $crawler = $client->request(
            Request::METHOD_GET,
            '/main',
        );

        self::assertResponseIsSuccessful();
        $images = $crawler->filter('img[alt="QR-код для автентифікатора"]');
        $this->assertGreaterThan(0, $images);
    }
}
