<?php
declare(strict_types=1);


namespace App\Controller\UserActions;

use App\DTO\RegisterDto;
use App\Presenters\UserPresenter;
use App\Services\UserService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Routing\Attribute\Route;

class RegisterAction extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserPresenter $userPresenter,
    )
    {}

    #[Route("/api/auth/register", name: "api_register", methods: ["POST"])]
    #[OA\Post(
        description: "Creates a new user account based on provided data.",
        summary: "Register a new user",
        security: [],
        requestBody: new OA\RequestBody(
            description: "Data for the new user",
            required: true,
            content: new OA\JsonContent(ref: new Model(type: RegisterDto::class))
        ),
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 201,
                description: "User successfully registered",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "string", format:"uuid", example: "a1b2c3d4-e5f6-7890-1234-567890abcdef"),
                        new OA\Property(property: "role", type: "string", enum: ["ROLE_ADMIN", "ROLE_AGENT", "ROLE_CUSTOMER"], example: "ROLE_CUSTOMER"),
                        new OA\Property(property: "email", type: "string", format:"email", example: "newuser@example.com"),
                        new OA\Property(property: "name", type: "string", example: "John Doe"),
                        new OA\Property(property: "phone", type: "string", example: "+380991234567"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation failed (e.g., invalid email format, missing fields, password too short)"
            ),
            new OA\Response(
                response: 409,
                description: "User with this email already exists"
            )
        ]
    )]
    public function register(
        #[MapRequestPayload] RegisterDto $registerDto,
    ): JsonResponse
    {
        try {
            $newUser = $this->userService->register($registerDto);
            return new JsonResponse($this->userPresenter->present($newUser), Response::HTTP_CREATED, []);
        }catch (ConflictHttpException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], JsonResponse::HTTP_CONFLICT);

        }
    }
}
