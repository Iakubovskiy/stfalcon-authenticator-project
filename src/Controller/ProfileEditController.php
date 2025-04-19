<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\UpdateUserDto;
use App\Services\FileService;
use App\Services\UpdateUserService;
use App\Services\UserService;
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
        private readonly UserService $userService,
        private readonly UpdateUserService $updateUserService,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FileService $fileService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/edit/{id}', name: 'edit', methods: ['GET'])]
    public function edit(Uuid $id, Request $request): Response
    {
        $user = $this->userService->getUserById($id);
        $request->query->get('errorMessage');
        $request->query->get('invalidValue');
        return $this->render('edit/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/edit/{id}', name: 'editUser', methods: ['POST'])]
    public function editUser(Uuid $id, Request $request): Response
    {
        $clientId = $this->tokenStorage->getToken()?->getUserIdentifier();
        if ($clientId === null) {
            return new Response(status: 401);
        }

        if (! Uuid::fromString($clientId) ->equals($id)) {
            return new Response(status: 403);
        }

        /** @var string $email */
        $email = $request->request->get('email');
        /** @var ?string $passwordRaw */
        $passwordRaw = $request->request->get('password');
        /** @var ?string $password */
        $password = is_string($passwordRaw) ? $passwordRaw : null;
        /** @var ?UploadedFile $profilePictureFile */
        $profilePictureFile = $request->files->get('profile_picture');
        $filePath = null;

        if ($profilePictureFile !== null) {
            $filePath = $this->fileService->saveFile($profilePictureFile);
        }

        $updateUserDto = new UpdateUserDto(
            $email,
            $password,
            $filePath,
        );
        try {
            $this->updateUserService->updateUser($id, $updateUserDto);
        } catch (ValidationFailedException $e) {
            $user = $this->userService->getUserById($id);
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
