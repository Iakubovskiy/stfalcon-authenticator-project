<?php
declare(strict_types=1);


namespace App\User\Profile\UseCases;

use App\User\Profile\UseCases\Edit\FileService;
use App\User\Support\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;

class GetPhotoController extends AbstractController
{
    public function __construct(
        private readonly FileService $fileService,
        private readonly UserRepository $userRepository,
    )
    {

    }

    #[Route(path: '/photo', methods: ['GET'], name: 'user_photo')]
    public function getPhoto(#[CurrentUser] UserInterface $currentUser) : Response
    {
        $user = $this->userRepository->getUserById(Uuid::fromString($currentUser->getUserIdentifier()));
        $response = $this->fileService->getFile($user->getPhotoUrl());

        if (!$response) {
            return new Response(status: Response::HTTP_NOT_FOUND);
        }

        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->add(['Content-Type' => 'image/png']);

        return $response;
    }
}
