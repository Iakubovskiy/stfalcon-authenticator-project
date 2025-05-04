<?php

declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin\Types\Bootstrap;

use App\User\UseCases\Login\TwoFactorLogin\EncryptionService;
use App\User\UseCases\Login\TwoFactorLogin\Types\SecretKey;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Types\Type;

class SecretKeyBootstrap implements Middleware
{
    public function __construct(
        private readonly EncryptionService $encryptionService
    ) {

    }

    public function wrap(Driver $driver): Driver
    {
        /** @var SecretKey $type */
        $type = Type::getType(SecretKey::NAME);
        $type->setEncriptionService($this->encryptionService);
        return $driver;
    }
}
