<?php

declare(strict_types=1);

namespace App\Subscribers;

use App\Entity\User;
use App\Services\UpdateUserService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLoginSuccessEvent')]
readonly class LoginSubscriber
{
    public function __construct(
        private UpdateUserService $updateUserService
    ) {
    }

    public function onLoginSuccessEvent(LoginSuccessEvent $loginSuccessEvent): void
    {
        $user = $loginSuccessEvent->getUser();

        if (! $user instanceof User) {
            return;
        }

        $this->updateUserService->updateLastLogin($user->getId());
    }
}
