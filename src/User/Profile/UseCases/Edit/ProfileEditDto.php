<?php

declare(strict_types=1);

namespace App\User\Profile\UseCases\Edit;

use App\User\Email\Validator\UniqueEmail;
use Symfony\Component\Validator\Constraints as Assert;

readonly class ProfileEditDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[UniqueEmail]
        public string $email,
        public ?string $password,
        public ?string $photoPath
    ) {
    }
}
