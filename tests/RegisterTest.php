<?php

declare(strict_types=1);

namespace App\Tests;

use App\User\Domain\Support\UserRepository;
use App\User\Register\RegisterController;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(RegisterController::class)]
final class RegisterTest extends WebTestCase
{
    public function testRegisterPage(): void
    {
        $client = self::createClient();
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get(TranslatorInterface::class);

        $client->request(
            Request::METHOD_GET,
            '/register',
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('title', $translator->trans('twig.register_form.title'));
    }

    public function testRegistration(): void
    {
        $client = self::createClient();
        $client->request(
            Request::METHOD_POST,
            '/register',
            [
                'email' => 'registration@test.com',
                'password' => '123',
                'password_confirm' => '123',
            ]
        );

        self::assertResponseRedirects('/login');

        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy([
            'email.email' => 'registration@test.com',
        ]);

        self::assertNotNull($user);
    }
}
