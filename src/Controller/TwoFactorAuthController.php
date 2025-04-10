<?php
declare(strict_types=1);


namespace App\Controller;

use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class TwoFactorAuthController extends AbstractController
{
    public function __construct(private readonly UserService $userService)
    {

    }
    #[Route('/2fa', name: '2fa_login')]
    public function twoFactor(): Response
    {
        return $this->render('security/2fa_form.html.twig');
    }

    #[Route('/disable-2fa', name: 'disable_2fa', methods: ['POST'])]
    public function disableTwoFactor(Request $request): Response
    {
        $password = $request->request->get('password');
        $id = Uuid::fromString($request->request->get('id'));
        $success = $this->userService->disableTwoFactorAuthentication($id, $password);
        if ($success) {
            $this->addFlash('warning', 'Двофакторна автентифікація вимкнена.');
        } else {
            $this->addFlash('danger', 'Неправильний пароль. Спробуйте ще раз.');
        }
        return $this->redirectToRoute('main');
    }

    #[Route('/enable-2fa', name: 'enable_2fa', methods: ['POST'])]
    public function enableTwoFactor(Request $request): Response
    {
        $password = $request->request->get('password');
        $id = Uuid::fromString($request->request->get('id'));
        $success = $this->userService->enableTwoFactorAuthentication($id, $password);
        if ($success) {
            $this->addFlash('success', 'Двофакторна автентифікація ввімкнена.');
        } else {
            $this->addFlash('danger', 'Неправильний пароль. Спробуйте ще раз.');
        }
        return $this->redirectToRoute('main');
    }

    #[Route('/qr-secret/{id}', name: 'qr_secret', methods: ['GET'])]
    public function qrSecret(Uuid $id): Response
    {
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $this->userService->getUserQrCodeData($id),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            labelText: 'QR code for authenticator apps',
            labelFont: new OpenSans(15),
            labelAlignment: LabelAlignment::Center,
            logoResizeToWidth: 50,
            logoPunchoutBackground: true
        );
        $result = $builder->build();
        return new Response(
            $result->getString(),
            200,
            [
                'content-type' => 'image/png',
                'Content-Disposition' => 'inline; filename="qr-code.png"',
            ],
        );
    }
}
