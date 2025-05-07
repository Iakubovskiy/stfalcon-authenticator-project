<?php

declare(strict_types=1);

namespace App\User\Profile\UseCases\Get;

use DateInterval;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ProfilePageController extends AbstractController
{
    public function __construct(
        private readonly UriSigner $uriSigner,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ClockInterface $clock,
    ) {

    }

    #[Route(path: '/main', name: 'main')]
    public function main(): Response
    {
        $id = $this->tokenStorage->getToken()?->getUserIdentifier();
        $expireAt = $this->clock->now()->add(new DateInterval('PT10M'))->getTimestamp();
        $qrCodeUrl = $this->urlGenerator->generate(
            'qr_secret',
            [
                'id' => $id,
                'eat' => $expireAt,
            ],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );
        $signedQrUrl = $this->uriSigner->sign($qrCodeUrl);

        return $this->render(
            'main.html.twig',
            [
                'qrUrl' => $signedQrUrl,
            ]
        );
    }
}
