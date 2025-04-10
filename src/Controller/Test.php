<?php
declare(strict_types=1);


namespace App\Controller;

use App\Presenters\UserPresenter;
use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class Test extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserPresenter $userPresenter,
    )
    {}

    #[Route('api/secret/{id}', name: 'api_secret', methods: ['POST'])]
    public function createSecret(Uuid $id): JsonResponse
    {
        return new JsonResponse(
            $this->userPresenter->present($this->userService->getSecretTest($id)),
            Response::HTTP_OK,
            [],
        );
    }

    #[Route('api/secret/{id}', name: 'api_secret', methods: ['GET'])]
    public function getSecret(Uuid $id): JsonResponse
    {
        return new JsonResponse(
            [
                'secret' => $this->userService->getUserSecretKey($id),
            ],
            Response::HTTP_OK,
            []
        );
    }

    #[Route('api/verify/{id}', name: 'api_verify', methods: ['POST'])]
    public function verifyTotp(Uuid $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $totp = $data['totp'];
        return new JsonResponse($this->userService->verifyTotp($id, $totp), Response::HTTP_OK, []);
    }

    #[Route('/api/users', name: 'users', methods: ['GET'])]
    public function getUsers(): JsonResponse
    {
        return new JsonResponse($this->userPresenter->presentList($this->userService->getAllUsers()));
    }
}
