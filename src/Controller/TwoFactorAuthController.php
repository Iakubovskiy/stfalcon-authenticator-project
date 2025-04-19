<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\UserService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class TwoFactorAuthController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UriSigner $uriSigner,
        private readonly TranslatorInterface $translator,
    ) {

    }

    #[Route('/2fa', name: '2fa_login')]
    public function twoFactor(): Response
    {
        return $this->render('security/2fa_form.html.twig');
    }

    #[Route('/disable-2fa', name: 'disable_2fa', methods: ['POST'])]
    public function disableTwoFactor(Request $request): Response
    {
        /** @var string $password */
        $password = $request->request->get('password');
        /** @var string $id */
        $id = $request->request->get('id');
        $uuid = Uuid::fromString($id);
        $success = $this->userService->disableTwoFactorAuthentication($uuid, $password);
        if ($success) {
            $this->addFlash('warning', $this->translator->trans('warning.two_factor_off'));
        } else {
            $this->addFlash('danger', $this->translator->trans('errors.wrong_password'));
        }

        return $this->redirectToRoute('main');
    }

    #[Route('/enable-2fa', name: 'enable_2fa', methods: ['POST'])]
    public function enableTwoFactor(Request $request): Response
    {
        /** @var string $password */
        $password = $request->request->get('password');
        /** @var string $id */
        $id = $request->request->get('id');
        $uuid = Uuid::fromString($id);
        $success = $this->userService->enableTwoFactorAuthentication($uuid, $password);
        if ($success) {
            $this->addFlash('success', $this->translator->trans('success.two_factor_on'));
        } else {
            $this->addFlash('danger', $this->translator->trans('errors.wrong_password'));
        }

        return $this->redirectToRoute('main');
    }

    #[Route('/qr-secret/{id}', name: 'qr_secret', methods: ['GET'])]
    public function qrSecret(Uuid $id, Request $request): Response
    {
        $isValid = $this->uriSigner->checkRequest($request);
        if (! $isValid) {
            return new Response(
                status: 403
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
