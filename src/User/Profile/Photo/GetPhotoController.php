<?php
declare(strict_types=1);


namespace App\User\Profile\Photo;

use App\User\Support\UserRepository;
use Carbon\CarbonImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;

class GetPhotoController extends AbstractController
{
    public function __construct(
        private readonly GetFileService $fileService,
        private readonly UserRepository $userRepository,
        private readonly ClockInterface $clock,
        private readonly UriSigner $uriSigner,
    )
    {

    }

    #[Route(path: '/photo', methods: ['GET'], name: 'user_photo')]
    public function getPhoto(Request $request, #[CurrentUser] UserInterface $currentUser, #[MapQueryParameter('eat')] int $expirationTimestamp) : Response
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
