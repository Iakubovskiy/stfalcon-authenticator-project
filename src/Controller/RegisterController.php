<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\RegisterDto;
use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class RegisterController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService
    ) {

    }

    #[Route('/register', name: 'register')]
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

        } catch (ClientExceptionInterface $e) {
            $error = json_decode($e->getResponse()->getContent(false), true);
            $message = is_array($error) && isset($error['message']) ? $error['message'] : 'Помилка під час реєстрації';
            $this->addFlash('error', $message);
            return $this->redirectToRoute('register');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Невідома помилка');
            return $this->redirectToRoute('register');
        }
    }
}
