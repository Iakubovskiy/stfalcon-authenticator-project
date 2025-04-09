<?php
declare(strict_types=1);


namespace App\Controller\UserActions;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

class LoginAction extends AbstractController
{
    #[Route("/api/auth/login", name: "api_login", methods: ["POST"])]
    #[OA\Post(
        path: "/api/auth/login",
        description: "JWT Authentication",
        summary: "Login for user",
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username", "password"],
                properties: [
                    new OA\Property(property: "username", type: "string", example: "user@example.com"),
                    new OA\Property(property: "password", type: "string", example: "password")
                ]
            )
        ),
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "token", type: "string", example: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Wrong login or password"
            )
        ]
    )]
    public function fakeLogin(): JsonResponse
    {
        return new JsonResponse(['message'=>'just for swagger']);
    }
}
