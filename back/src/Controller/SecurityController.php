<?php

namespace App\Controller;

use App\Service\JwtService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

#[Route('/api')]
class SecurityController extends AbstractController
{
    public function __construct(
        private JwtService $jwtService
    ) {}

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return $this->json([
                'message' => 'Identifiants invalides.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->jwtService->createToken($user);

        return $this->json([
            'user' => $user->getUsername(),
            'roles' => $user->getRoles(),
            'token' => $token
        ]);
    }

    #[Route('/me', name: 'api_me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'user' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return $this->json(['message' => 'Déconnexion réussie']);
    }
}