<?php

declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin\Types;

use App\User\UseCases\Login\TwoFactorLogin\EncryptionService;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Override;
use Webmozart\Assert\Assert;

class SecretKey extends Type
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

        Assert::string($value);
        return $this->encryptionService->encryptSecret($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): string|null
    {
        /** @var string|null $value */
        $value = parent::convertToPHPValue($value, $platform);

        if ($value === null) {
            return null;
        }

        Assert::string($value);

        return $this->encryptionService->decryptSecret($value);
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
