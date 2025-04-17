<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomValidator;

class UpdateUserDto
{
    public function __construct(
        #[Assert\Email(
            message: "The email '{{ value }}' is not a valid email.",
            mode: Assert\Email::VALIDATION_MODE_STRICT
        )]
        #[CustomValidator\ConstrainUniqueEmail]
        public ?string $email,
        public ?string $password,
        public ?string $photoUrl
    ) {
    }
}
