<?php

declare(strict_types=1);

namespace App\Tests;

use App\User\Domain\Entity\User;
use App\User\Domain\Support\UserRepository;
use App\User\Profile\MainPageController;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

#[CoversClass(MainPageController::class)]
final class MainControllerTest extends WebTestCase
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
