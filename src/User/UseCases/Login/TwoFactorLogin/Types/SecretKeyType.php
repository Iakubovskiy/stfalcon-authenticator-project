<?php

declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin\Types;

use App\User\SecretKey\SecretKey;
use App\User\UseCases\Login\TwoFactorLogin\EncryptionService;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Override;
use ReflectionClass;
use Webmozart\Assert\Assert;

class SecretKeyType extends Type
{
    public const NAME = 'secret_key';

    private EncryptionService $encryptionService;

    public function setEncriptionService(EncryptionService $encryptionService): void
    {
        $this->encryptionService = $encryptionService;
    }

    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): string|null
    {
        if ($value === null) {
            return null;
        }

        /** @var SecretKey $value */
        Assert::isInstanceOf($value, SecretKey::class);
        return $this->encryptionService->encryptSecret($value->getSecretKey());
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?SecretKey
    {
        /** @var string|null $value */
        $value = parent::convertToPHPValue($value, $platform);

        if ($value === null) {
            return null;
        }

        Assert::string($value);

        $reflectionClass = new ReflectionClass(SecretKey::class);
        /** @var SecretKey $lazySecretGhost */
        $lazySecretGhost = $reflectionClass->newLazyGhost(function (SecretKey $secretKey) use ($value): void {
            $secretKey->__construct($this->encryptionService->decryptSecret($value));
        });

        return $lazySecretGhost;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    #[Override]
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
