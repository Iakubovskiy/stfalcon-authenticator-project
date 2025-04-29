<?php

declare(strict_types=1);

namespace App\User\Register;

use App\User\Domain\Validator as CustomValidator;
use Symfony\Component\Validator\Constraints as Assert;

readonly class RegisterDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[CustomValidator\UniqueEmail]
        public string $email,
        #[Assert\NotBlank]
        public string $password,
        #[Assert\NotBlank]
        #[Assert\EqualTo(propertyPath: 'password')]
        public string $passwordConfirmation,
    ) {
    }
}
