<?php

declare(strict_types=1);

namespace App\User\UseCases\Login\Subscribers;

use App\User\UseCases\Login\UpdateLastLoginService;
use App\User\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLoginSuccessEvent')]
readonly class LoginSubscriber
{
    public function __construct(
        private UpdateLastLoginService $updateLastLoginService
    ) {
    }

    public function onLoginSuccessEvent(LoginSuccessEvent $loginSuccessEvent): void
    {
        $user = $loginSuccessEvent->getUser();

        if (! $user instanceof User) {
            return;
        }

        $this->updateLastLoginService->updateLastLogin($user->getId());
    }
}
