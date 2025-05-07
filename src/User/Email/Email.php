<?php

declare(strict_types=1);

namespace App\User\Email;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Embeddable]
readonly class Email
{
    private function __construct(
        #[ORM\Column(unique: true)]
        private string $email,
    ) {
        $this->ensureIsValidEmail($email);
    }

    public static function fromString(string $email): self
    {
        return new self($email);
    }

    public function asString(): string
    {
        return $this->email;
    }

    private function ensureIsValidEmail(string $email): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" is not a valid email address',
                    $email
                )
            );
        }
    }
}
