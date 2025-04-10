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

    public function presentList(array $users): array
    {
        return array_map(fn ($user) => $this->present($user),$users );
    }
}
