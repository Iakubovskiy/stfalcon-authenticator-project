<?php
declare(strict_types=1);


namespace App\Subscribers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

readonly class TwoFactorRateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(private RateLimiterFactory $rateLimiterFactory)
    {}

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', 10],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->getPathInfo() !== '/2fa_check' || !$request->isMethod('POST')) {
            return;
        }
        $ip = $request->getClientIp() ?? 'unknown';
        $limiter = $this->rateLimiterFactory->create($ip);

        $limit = $limiter->consume(1);
        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException(null, 'Забагато спроб. Спробуйте пізніше.');
        }
    }
}
