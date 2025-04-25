<?php

declare(strict_types=1);

namespace App\Tests;

use App\Controller\MainPageController;
use App\Repository\UserRepository;
use App\Services\UpdateUserService;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;
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
        /** @var UpdateUserService $updateUserService */
        $updateUserService = self::getContainer()->get(UpdateUserService::class);
        $user = $userRepository->find(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));
        $updateUserService->updateLastLogin(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));

        if ($user === null) {
            throw new RuntimeException('No user found');
        }

        $client->loginUser($user);
        $crawler = $client->request(
            Request::METHOD_GET,
            'main',
        );

        self::assertResponseIsSuccessful();
        $images = $crawler->filter('img[alt="QR-код для автентифікатора"]');
        $this->assertGreaterThan(0, $images);
    }
}
