<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validator as CustomValidator;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateUserDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[CustomValidator\UniqueEmail]
        public string $email,
        public ?string $password,
        public ?string $photoPath
    ) {
    }
}
