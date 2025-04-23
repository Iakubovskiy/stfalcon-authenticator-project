<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validator as CustomValidator;
use Symfony\Component\Validator\Constraints as Assert;

readonly class RegisterDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email(
            message: 'errors.not_valid_email',
            mode: Assert\Email::VALIDATION_MODE_STRICT
        )]
        #[CustomValidator\UniqueEmail]
        public string $email,
        #[Assert\NotBlank]
        public string $password,
    ) {
    }
}
