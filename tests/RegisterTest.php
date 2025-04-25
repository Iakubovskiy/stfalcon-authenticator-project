<?php

declare(strict_types=1);

namespace App\Tests;

use App\Repository\UserRepository;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegisterTest extends WebTestCase
{
    public function testRegisterPage(): void
    {
        $client = static::createClient();
        /**@var TranslatorInterface $translator*/
        $translator = self::getContainer()->get(TranslatorInterface::class);

        $crawler = $client->request(
            Request::METHOD_GET,
            'register',
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('title', $translator->trans('twig.register_form.title'));
    }

    public function testRegistration()
    {
        $client = static::createClient();
        /**@var TranslatorInterface $translator*/

        $crawler = $client->request(
            Request::METHOD_POST,
            'register',
            [
                'email' => 'registration@test.com',
                'password' => '123',
                'password_confirm' => '123'
            ]
        );

        self::assertResponseRedirects('/login');
    }
}
