<?php

declare(strict_types=1);

namespace App\Tests;

use App\Controller\LoginController;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(LoginController::class)]
final class LoginTest extends WebTestCase
{
    public function testLoginPage(): void
    {
        $client = self::createClient();
        self::getContainer()->get(UserRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get(TranslatorInterface::class);

        $client->request(
            Request::METHOD_GET,
            '/login',
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('title', $translator->trans('twig.login_form.title'));
    }
}
