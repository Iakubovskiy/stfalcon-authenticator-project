<?php

declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin;

use Carbon\CarbonImmutable;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class TwoFactorAuthController extends AbstractController
{
    public function __construct(
        private readonly TwoFactorService $userService,
        private readonly UriSigner $uriSigner,
        private readonly TranslatorInterface $translator,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ClockInterface $clock,
    ) {

    }

    #[Route('/2fa', name: '2fa_login')]
    public function twoFactor(): Response
    {
        return $this->render('security/2fa_form.html.twig');
    }

    #[Route('/2fa/disable', name: 'disable_2fa', methods: ['POST'])]
    public function disableTwoFactor(Request $request): Response
    {
        /** @var string $password */
        $password = $request->request->get('password');
        if ($this->tokenStorage->getToken() instanceof TokenInterface) {
            $id = Uuid::fromString($this->tokenStorage->getToken()->getUserIdentifier());
        } else {
            return new Response(status: Response::HTTP_UNAUTHORIZED);
        }

        $success = $this->userService->disableTwoFactorAuthentication($id, $password);
        if ($success) {
            $this->addFlash('warning', $this->translator->trans('warning.two_factor_off'));
        } else {
            $this->addFlash('danger', $this->translator->trans('errors.wrong_password'));
        }

        return $this->redirectToRoute('main');
    }

    #[Route('/2fa/enable', name: 'enable_2fa', methods: ['POST'])]
    public function enableTwoFactor(Request $request): Response
    {
        /** @var string $password */
        $password = $request->request->get('password');
        if ($this->tokenStorage->getToken() instanceof TokenInterface) {
            $id = Uuid::fromString($this->tokenStorage->getToken()->getUserIdentifier());
        } else {
            return new Response(status: Response::HTTP_UNAUTHORIZED);
        }

        $success = $this->userService->enableTwoFactorAuthentication($id, $password);
        if ($success) {
            $this->addFlash('success', $this->translator->trans('success.two_factor_on'));
        } else {
            $this->addFlash('danger', $this->translator->trans('errors.wrong_password'));
        }

        return $this->redirectToRoute('main');
    }

    #[Route('/2fa/qr-secret/{id}', name: 'qr_secret', methods: ['GET'])]
    public function qrSecret(Uuid $id, Request $request, #[MapQueryParameter('eat')] int $expirationTimestamp): Response
    {
        $expireAt = CarbonImmutable::createFromTimestamp($expirationTimestamp);
        if ($expireAt->lessThan($this->clock->now())) {
            return new Response(
                content: 'expired',
                status: Response::HTTP_FORBIDDEN
            );
        }

        $isValid = $this->uriSigner->checkRequest($request);
        if (! $isValid) {
            return new Response(
                status: Response::HTTP_FORBIDDEN
            );
        }

        $builder = new Builder(
            data: $this->userService->getUserQrCodeData($id),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            labelText: $this->translator->trans('qr.qr_label'),
            logoResizeToWidth: 50,
            logoPunchoutBackground: true
        );
        $result = $builder->build();
        return new Response(
            $result->getString(),
            Response::HTTP_OK,
            [
                'content-type' => 'image/png',
                'Content-Disposition' => 'inline; filename="qr-code.png"',
            ],
        );
    }
}
