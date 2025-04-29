<?php

declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class EncryptionService
{
    public function __construct(
        #[Autowire(env: 'ENCRYPTION_KEY')]
        private string $rawKey,
    ) {

    }

    public function encryptSecret(string $data): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $key = sodium_crypto_generichash(
            $this->rawKey,
            '',
            SODIUM_CRYPTO_SECRETBOX_KEYBYTES
        );
        $cipher = sodium_crypto_secretbox(
            $data,
            $nonce,
            $key
        );
        return base64_encode($nonce . $cipher);
    }

    public function decryptSecret(string $data): string
    {
        /** @var string $decodedData */
        $decodedData = base64_decode($data, true);
        $nonce = mb_substr($decodedData, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $cipher = mb_substr($decodedData, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
        $key = sodium_crypto_generichash(
            $this->rawKey,
            '',
            SODIUM_CRYPTO_SECRETBOX_KEYBYTES
        );
        /** @var string $decryptedSecret */
        $decryptedSecret = sodium_crypto_secretbox_open(
            $cipher,
            $nonce,
            $key
        );
        return $decryptedSecret;
    }
}
