<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Log\LoggerInterface;

class JwtAuthenticator extends AbstractAuthenticator
{
    private string $jwtSecret;
    private LoggerInterface $logger;

    public function __construct(string $jwtSecret, LoggerInterface $logger)
    {
        if (empty($jwtSecret)) {
            throw new \RuntimeException('JWT_SECRET must be configured');
        }
        $this->jwtSecret = $jwtSecret;
        $this->logger = $logger;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        try {
            $authHeader = $request->headers->get('Authorization');
            if (null === $authHeader) {
                throw new CustomUserMessageAuthenticationException('No Authorization header provided');
            }

            if (!preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
                throw new CustomUserMessageAuthenticationException('Invalid Authorization header format');
            }

            $token = $matches[1];
            $jwt = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

            if (!isset($jwt->username)) {
                throw new CustomUserMessageAuthenticationException('Invalid JWT token structure');
            }

            return new SelfValidatingPassport(
                new UserBadge($jwt->username)
            );
        } catch (\Exception $exception) {
            $this->logger->error('JWT Authentication failed: ' . $exception->getMessage());
            throw new CustomUserMessageAuthenticationException('Authentication failed');
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
} 