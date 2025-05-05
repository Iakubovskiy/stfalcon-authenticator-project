<?php

declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin\Types\Bootstrap;

use App\User\UseCases\Login\TwoFactorLogin\EncryptionService;
use App\User\UseCases\Login\TwoFactorLogin\Types\SecretKeyType;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Types\Type;

final readonly class SecretKeyBootstrap implements Middleware
{
    public function __construct(
        private EncryptionService $encryptionService
    ) {

    }

    public function wrap(Driver $driver): Driver
    {
        /** @var SecretKeyType $type */
        $type = Type::getType(SecretKeyType::NAME);
        $type->setEncriptionService($this->encryptionService);
        return $driver;
    }
}
