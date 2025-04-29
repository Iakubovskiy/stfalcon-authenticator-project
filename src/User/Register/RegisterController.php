<?php

declare(strict_types=1);

namespace App\User\Register;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
class RegisterController extends AbstractController
{
    public function __construct(
        private readonly RegisterService $registerService,
        private readonly TranslatorInterface $translator,
    ) {

    }

    #[Route('/register', name: 'register', methods: ['GET'])]
    public function register(): Response
    {
        return $this->render('auth/register.html.twig');
    }

    #[Route('/register', name: 'register_process', methods: ['POST'])]
    public function registerProcess(Request $request): Response
    {
        $email = $request->request->getString('email');
        $password = $request->request->getString('password');
        $passwordConfirm = $request->request->getString('password_confirm');

        $registerDTO = new RegisterDto($email, $password, $passwordConfirm);
        try {
            $this->registerService->register($registerDTO);
            $this->addFlash('success', $this->translator->trans('success.register'));
        } catch (ValidationFailedException $e) {
            $errors = $e->getViolations();
            return $this->render('auth/register.html.twig', [
                'errors' => $errors,
            ]);
        }

        return $this->redirectToRoute('login');
    }
}
