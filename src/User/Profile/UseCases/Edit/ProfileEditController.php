<?php

declare(strict_types=1);

namespace App\User\Profile\UseCases\Edit;

use App\User\Support\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileEditController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ProfileEditService $updateUserService,
        private readonly FileService $fileService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/edit', name: 'edit', methods: ['GET'])]
    public function edit(#[CurrentUser] UserInterface $currentUser): Response
    {
        $user = $this->userRepository->getUserById(Uuid::fromString($currentUser->getUserIdentifier()));
        return $this->render('edit/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/edit', name: 'editUser', methods: ['POST'])]
    public function editUser(Request $request, #[CurrentUser] UserInterface $user): Response
    {
        $id = Uuid::fromString($user->getUserIdentifier());

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
        return $this->redirectToRoute('edit');
    }
}
