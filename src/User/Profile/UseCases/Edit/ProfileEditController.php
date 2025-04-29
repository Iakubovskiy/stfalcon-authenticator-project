<?php

declare(strict_types=1);

namespace App\User\Profile\UseCases\Edit;

use App\User\Support\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileEditController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ProfileEditService $updateUserService,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FileService $fileService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/edit/{id}', name: 'edit', methods: ['GET'])]
    public function edit(Uuid $id): Response
    {
        $user = $this->userRepository->getUserById($id);
        return $this->render('edit/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/edit/{id}', name: 'editUser', methods: ['POST'])]
    public function editUser(Uuid $id, Request $request): Response
    {
        $clientId = $this->tokenStorage->getToken()?->getUserIdentifier();
        if ($clientId === null) {
            return new Response(status: Response::HTTP_UNAUTHORIZED);
        }

        if (! Uuid::fromString($clientId) ->equals($id)) {
            return new Response(status: Response::HTTP_FORBIDDEN);
        }

        $email = $request->request->getString('email');
        $passwordRaw = $request->request->getString('password');
        $password = empty($passwordRaw) ? null : $passwordRaw;
        /** @var ?UploadedFile $profilePictureFile */
        $profilePictureFile = $request->files->get('profile_picture');
        $filePath = null;

        if ($profilePictureFile !== null) {
            $filePath = $this->fileService->saveFile($profilePictureFile);
        }

        $profileEditDto = new ProfileEditDto(
            $email,
            $password,
            $filePath,
        );
        try {
            $this->updateUserService->updateUser($id, $profileEditDto);
        } catch (ValidationFailedException $e) {
            $user = $this->userRepository->getUserById($id);
            return $this->render('edit/edit.html.twig', [
                'user' => $user,
                'errors' => $e->getViolations(),
            ]);
        }

        $this->addFlash('success', $this->translator->trans('success.update'));
        return $this->redirectToRoute('edit', [
            'id' => $id,
        ]);
    }
}
