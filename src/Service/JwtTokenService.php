<?php

namespace App\Service;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class JwtTokenService
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager
    ) {
    }

    public function generateToken(User $user): string
    {
        return $this->jwtManager->create($user);
    }

    public function decodeToken(string $token): array
    {
        return $this->jwtManager->parse($token);
    }

    public function getTokenExpiration(string $token): ?\DateTimeImmutable
    {
        $payload = $this->decodeToken($token);
        
        if (isset($payload['exp'])) {
            return (new \DateTimeImmutable())->setTimestamp($payload['exp']);
        }
        
        return null;
    }

    public function isTokenValid(string $token): bool
    {
        try {
            $payload = $this->decodeToken($token);

            return isset($payload['exp']) && $payload['exp'] > time();
        } catch (\Exception $e) {
            return false;
        }
    }
}
