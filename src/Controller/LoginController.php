<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    public function __construct(
        private readonly UriSigner $uriSigner,
        private UrlGeneratorInterface $urlGenerator
    ) {

    }

    #[Route(path: '/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/main', name: 'main')]
    public function test(): Response
    {
        $qrCodeUrl = $this->urlGenerator->generate(
            'qr_secret',
            [
                'id' => '0196158b-a5bf-7f06-96be-ec13aa7f6902',
            ],
        );
        //        dd($qrCodeUrl);
        $signedUrl = $this->uriSigner->sign($qrCodeUrl);
        return $this->render(
            'main.html.twig',
            [
                'url' => $signedUrl,
            ]
        );
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(Security $security): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
