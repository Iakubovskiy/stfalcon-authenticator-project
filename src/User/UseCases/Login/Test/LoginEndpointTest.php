<?php

declare(strict_types=1);

namespace App\User\UseCases\Login\Test;

use App\User\UseCases\Login\LoginController;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(LoginController::class)]
class LoginEndpointTest extends WebTestCase
{
    public function testLoginEndpoint(): void
    {
        $client = static::createClient();

        $client->request('POST', '/login',[
            '_username' => 'test@example.com',
            '_password' => '123',
        ]);

        self::assertResponseRedirects('/main');
    }
}
