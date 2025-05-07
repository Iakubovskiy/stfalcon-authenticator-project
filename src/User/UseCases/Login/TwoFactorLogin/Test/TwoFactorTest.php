<?php

declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin\Test;

use App\User\Support\UserRepository;
use App\User\UseCases\Login\TwoFactorLogin\TwoFactorAuthController;
use App\User\User;
use Carbon\CarbonImmutable;
use DateInterval;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

#[CoversClass(TwoFactorAuthController::class)]
final class TwoFactorTest extends WebTestCase
{
    use ClockSensitiveTrait;

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
        $this->assertInstanceof(User::class, $user);
        if ($user->isTotpAuthenticationEnabled()) {
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
        $this->assertInstanceof(User::class, $user);
        if (! $user->isTotpAuthenticationEnabled()) {
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

    public function testQrEndpoint(): void
    {
        $client = self::createClient();

        self::mockTime(CarbonImmutable::parse('2025-04-25 15:25:00+03:00'));

        $client->request(
            Request::METHOD_GET,
            '/2fa/qr-secret/01966bea-7668-7e04-b154-68d97490782e?_hash=T%2Bm53erHteVYGt1Gb%2FPC5CiUEaTNl47OmxhT44KxXhE%3D&eat=1745583902',
        );

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'image/png');
    }

    public function testQrEndpointNotSingedUrl(): void
    {
        $client = self::createClient();
        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        /** @var ClockInterface $clock */
        $clock = self::getContainer()->get(ClockInterface::class);
        $expireAt = $clock->now()->add(new DateInterval('PT10M'))->getTimestamp();

        $qrCodeUrl = $urlGenerator->generate(
            'qr_secret',
            [
                'id' => Uuid::fromString('017f22e2-79b0-7cc0-98a0-0c0f6a9b38d3'),
                'eat' => $expireAt,
            ],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );

        $client->request(
            Request::METHOD_GET,
            $qrCodeUrl,
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testQrEndpointNotValidExpirationTimestamp(): void
    {
        $client = self::createClient();

        self::mockTime(CarbonImmutable::parse('2025-05-25 15:25:00+03:00'));

        $client->request(
            Request::METHOD_GET,
            '/2fa/qr-secret/01966bea-7668-7e04-b154-68d97490782e?_hash=T%2Bm53erHteVYGt1Gb%2FPC5CiUEaTNl47OmxhT44KxXhE%3D&eat=1745583902',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
