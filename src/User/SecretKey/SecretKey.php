<?php

declare(strict_types=1);

namespace App\User\SecretKey;

final readonly class SecretKey
{
    public function __construct(
        private string $secretKey,
    ) {

    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }
}
