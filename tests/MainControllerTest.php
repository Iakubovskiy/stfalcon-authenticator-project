<?php

declare(strict_types=1);

namespace App\Tests;

use App\Repository\UserRepository;
use App\Services\UpdateUserService;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class MainControllerTest extends WebTestCase
{
    public function testMainPage(): void
    {
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        /** @var UpdateUserService $updateUserService */
        $updateUserService = self::getContainer()->get(UpdateUserService::class);
        $user = $userRepository->find(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));
        $updateUserService->updateLastLogin(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));

        if ($user === null) {
            throw new RuntimeException('No user found');
        }
        /**@var TranslatorInterface $translator*/

        $client->loginUser($user);
        $crawler = $client->request(
            Request::METHOD_GET,
            'main',
        );

        self::assertResponseIsSuccessful();
        $images = $crawler->filter('img[alt="QR-код для автентифікатора"]');
        self::assertGreaterThan(
            0,
            $images,
        );
    }
}
