<?php
declare(strict_types=1);


namespace App\Subscribers;

use App\Entity\User;
use App\Services\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

readonly class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserService $userService)
    {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => ['onRequest', 10],
        ];
    }

    public function onRequest(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $this->userService->updateLastLogin($user->getId());
    }
}
