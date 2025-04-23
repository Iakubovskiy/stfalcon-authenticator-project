<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validator as CustomValidator;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserDto
{
    public function __construct(
        #[Assert\Email(
            message: 'errors.not_valid_email',
            mode: Assert\Email::VALIDATION_MODE_STRICT
        )]
        #[CustomValidator\UniqueEmail]
        public ?string $email,
        public ?string $password,
        public ?string $photoPath
    ) {
    }
}
