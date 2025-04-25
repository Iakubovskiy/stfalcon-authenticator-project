<?php

declare(strict_types=1);

namespace App\Tests;

use App\Repository\UserRepository;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginTest extends WebTestCase
{
    public function testLoginPage(): void
    {
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->find(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));

        if ($user === null) {
            throw new RuntimeException('No user found');
        }
        /**@var TranslatorInterface $translator*/
        $translator = self::getContainer()->get(TranslatorInterface::class);

        $client->loginUser($user);
        $crawler = $client->request(
            Request::METHOD_GET,
            'login',
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('title', $translator->trans('twig.login_form.title'));
    }
}
