<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\RegisterDto;
use App\Services\RegisterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        /** @var string $email */
        $email = $request->request->get('email');
        /** @var string $password */
        $password = $request->request->get('password');
        // string
        $passwordConfirm = $request->request->get('password_confirm');

        if ($password !== $passwordConfirm) {
            $this->addFlash('error', $this->translator->trans('errors.passwords_do_not_match'));
            return $this->redirectToRoute('register');
        }

        $registerDTO = new RegisterDTO($email, $password);
        try {
            $this->registerService->register($registerDTO);
            $this->addFlash('success', $this->translator->trans('success.register'));

            return $this->redirectToRoute('login');

        } catch (ValidationFailedException $e) {
            $errors = $e->getViolations();
            return $this->render('auth/register.html.twig', [
                'errors' => $errors,
            ]);
        }
    }
}
