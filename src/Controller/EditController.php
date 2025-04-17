<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\UpdateUserDto;
use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class EditController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService
    ) {
    }

    #[Route(path: 'edit/{id}', name: 'edit', methods: ['GET'])]
    public function edit(Uuid $id, Request $request): Response
    {
        $user = $this->userService->getUserById($id);
        /** @var ?string $errorMessage*/
        $errorMessage = $request->query->get('errorMessage');
        /** @var ?string $invalidValue*/
        $invalidValue = $request->query->get('invalidValue');
        return $this->render('edit/edit.html.twig', [
            'user' => $user,
            'errorMessage' => $errorMessage,
            'invalidValue' => $invalidValue,
        ]);
    }

    #[Route(path: '/edit/{id}', name: 'editUser', methods: ['POST'])]
    public function editUser(Uuid $id, Request $request): Response
    {
        /** @var string $email */
        $email = $request->request->get('email');
        $passwordRaw = $request->request->get('password');
        /** @var ?string $password */
        $password = is_string($passwordRaw) ? $passwordRaw : null;
        /** @var ?UploadedFile $profilePictureFile */
        $profilePictureFile = $request->files->get('profile_picture');
        $filePath = '';
        // move into dedicated file storage service

        if ($profilePictureFile !== null) {
            $originalFilename = pathinfo($profilePictureFile->getClientOriginalName(), PATHINFO_FILENAME);
            $profilePictureFilename = $originalFilename . '-' . uniqid() . '.' . $profilePictureFile->guessExtension();
            try {
                /** @var string $rootDirectory */
                $rootDirectory = $this->getParameter('profile_pictures_directory'); // autowire
                $profilePictureFile->move(
                    $rootDirectory,
                    $profilePictureFilename
                );
                $filePath = $profilePictureFilename;
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Помилка при завантаженні фото');
                return $this->redirectToRoute('edit', [
                    'id' => $id,
                ]);
            }
        }

        $updateUserDto = new UpdateUserDto(
            $email,
            $password,
            $filePath,
        );
        try {
            $this->userService->updateUser($id, $updateUserDto);
        } catch (ValidationFailedException $e) {
            return $this->redirectToRoute('edit', [
                'id' => $id,
                'errorMessage' => $e->getViolations()[0]->getMessage(),
                'invalidValue' => $e->getViolations()[0]->getInvalidValue(),
            ]);
        }
        $this->addFlash('success', 'Успішно оновлено');
        return $this->redirectToRoute('edit', [
            'id' => $id,
        ]);
    }
}
