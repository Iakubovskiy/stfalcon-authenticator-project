<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MainPageController extends AbstractController
{
    public function __construct(
        private readonly UriSigner $uriSigner,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TokenStorageInterface $tokenStorage,
    ) {

    }

    #[Route(path: '/main', name: 'main')]
    public function main(): Response
    {
        $id = $this->tokenStorage->getToken()->getUserIdentifier();
        $qrCodeUrl = $this->urlGenerator->generate(
            'qr_secret',
            [
                'id' => $id,
            ],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );

        $signedUrl = $this->uriSigner->sign($qrCodeUrl);
        return $this->render(
            'main.html.twig',
            [
                'url' => $signedUrl,
            ]
        );
    }
}
