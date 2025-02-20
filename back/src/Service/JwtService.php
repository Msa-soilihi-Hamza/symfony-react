<?php

namespace App\Service;

use App\Entity\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class JwtService
{
    private string $jwtSecret;
    private int $tokenTtl;

    public function __construct(ParameterBagInterface $params)
    {
        $secret = $params->get('app.jwt_secret');
        if (empty($secret)) {
            throw new \RuntimeException('JWT_SECRET must be configured in .env');
        }
        $this->jwtSecret = $secret;
        $this->tokenTtl = $params->get('app.jwt_ttl');
    }

    public function createToken(User $user): string
    {
        if (empty($this->jwtSecret)) {
            throw new \RuntimeException('JWT secret is not configured');
        }

        $payload = [
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
            'iat' => time(),
            'exp' => time() + $this->tokenTtl
        ];

        try {
            return \Firebase\JWT\JWT::encode($payload, $this->jwtSecret, 'HS256');
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to create JWT token: ' . $e->getMessage());
        }
    }

    public function validateToken(string $token): bool
    {
        try {
            if (empty($token)) {
                return false;
            }
            \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($this->jwtSecret, 'HS256'));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getTokenData(string $token): array
    {
        try {
            if (empty($token)) {
                return [];
            }
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($this->jwtSecret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return [];
        }
    }
} 