<?php

declare(strict_types=1);

namespace App\Tests;

use App\Repository\UserRepository;
use LogicException;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Uid\Uuid;

class TwoFactorTest extends WebTestCase
{
    public function testDisableTwoFactor(): void
    {
        $client = self::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->find(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));
        if ($user === null) {
            throw new RuntimeException('No user found');
        }

        $client->loginUser($user);
        $client->request(
            Request::METHOD_POST,
            '/2fa/disable',
            [
                'password' => '123',
            ],
        );

        self::assertResponseRedirects('/main');
        $user = $userRepository->find(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));
        if($user->isTotpAuthenticationEnabled()){
            throw new LogicException('2 factor auth must be off');
        }
    }

    public function testDisableTwoFactorNoUser(): void
    {
        $client = self::createClient();
        $client->request(
            Request::METHOD_POST,
            '/2fa/disable',
            [
                'password' => '123',
            ],
        );

        self::assertResponseRedirects('/login');
    }

    public function testEnableTwoFactor(): void
    {
        $client = self::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->find(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));
        if ($user === null) {
            throw new RuntimeException('No user found');
        }

        $client->loginUser($user);
        $client->request(
            Request::METHOD_POST,
            '/2fa/enable',
            [
                'password' => '123',
            ],
        );

        self::assertResponseRedirects('/main');
        $user = $userRepository->find(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));
        if(!$user->isTotpAuthenticationEnabled()){
            throw new LogicException('2 factor auth must be on');
        }
    }

    public function testEnableTwoFactorNoUser(): void
    {
        $client = self::createClient();
        $client->request(
            Request::METHOD_POST,
            '/2fa/enable',
            [
                'password' => '123',
            ],
        );

        self::assertResponseRedirects('/login');
    }

    public function testQrEndpoint()
    {
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->find(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));
        if ($user === null) {
            throw new RuntimeException('No user found');
        }

        $client->loginUser($user);

        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        $uriSigner = self::getContainer()->get(UriSigner::class);

        $qrCodeUrl = $urlGenerator->generate(
            'qr_secret',
            [
                'id' => Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'),
            ],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );
        $signedUrl = $uriSigner->sign($qrCodeUrl);

        $client->request(
            Request::METHOD_GET,
            $signedUrl,
        );

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'image/png');
    }

    public function testQrEndpointNotSingedUrl()
    {
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->find(Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'));
        if ($user === null) {
            throw new RuntimeException('No user found');
        }

        $client->loginUser($user);

        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        $uriSigner = self::getContainer()->get(UriSigner::class);

        $qrCodeUrl = $urlGenerator->generate(
            'qr_secret',
            [
                'id' => Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'),
            ],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );

        $client->request(
            Request::METHOD_GET,
            $qrCodeUrl,
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
