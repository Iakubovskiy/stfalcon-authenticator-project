<?php
declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class RegisterDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email(
            message: "The email '{{ value }}' is not a valid email.",
            mode: Assert\Email::VALIDATION_MODE_STRICT
        )]
        public string $email,
        #[Assert\NotBlank]
        public string $password,
    ){}
}
