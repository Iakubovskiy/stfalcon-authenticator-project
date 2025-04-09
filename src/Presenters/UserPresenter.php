<?php
declare(strict_types=1);


namespace App\Presenters;

use App\Entity\User;

final readonly class UserPresenter
{

    public function __construct(){}
    public function present(User $user): array
    {
        return [
          'id' => $user->getId(),
          'email' => $user->getEmail(),
          'roles' => $user->getRoles(),
        ];
    }
}
