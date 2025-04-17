<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\RegisterDto;
use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class RegisterController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService
    ) {

    }

    #[Route('/register', name: 'register', methods: ['GET'])]
    public function register(): Response
    {
        return $this->render('auth/register.html.twig');
    }

    #[Route('/register/process', name: 'register_process', methods: ['POST'])]
    public function registerProcess(Request $request): Response
    {
        /** @var string $email */
        $email = $request->request->get('email');
        /** @var string $password */
        $password = $request->request->get('password');
        // string
        $passwordConfirm = $request->request->get('password_confirm');

        if ($password !== $passwordConfirm) {
            $this->addFlash('error', 'Паролі не збігаються.');
            return $this->redirectToRoute('register');
        }

        $registerDTO = new RegisterDTO($email, $password);
        try {
            $this->userService->register($registerDTO);
            $this->addFlash('success', 'Реєстрація успішна. Увійдіть у свій акаунт.');

            return $this->redirectToRoute('login');

        } catch (ValidationFailedException $e) {
            $message = $e->getViolations()[0]->getMessage();
            $this->addFlash('danger', $message);
            return $this->redirectToRoute('register');
        }
    }
}
