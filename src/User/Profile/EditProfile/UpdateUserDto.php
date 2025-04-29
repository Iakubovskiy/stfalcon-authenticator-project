<?php

declare(strict_types=1);

namespace App\User\Profile\EditProfile;

use App\User\Domain\Validator\UniqueEmail;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateUserDto
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
