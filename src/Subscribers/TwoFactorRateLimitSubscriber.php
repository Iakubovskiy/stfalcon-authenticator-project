<?php

declare(strict_types=1);

namespace App\Subscribers;

use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener(event: RequestEvent::class, method: 'onRequest')]
readonly class TwoFactorRateLimitSubscriber
{
    public function __construct(
        #[Autowire('@limiter.two_factor_login')]
        private RateLimiterFactory $limiter,
        private TranslatorInterface $translator,
    ) {
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->getPathInfo() !== '/2fa_check' || ! $request->isMethod('POST')) {
            return;
        }

        $ip = $request->getClientIp();
        if ($ip === null) {
            throw new RuntimeException($this->translator->trans('errors.ip_not_found'));
        }

        $limiter = $this->limiter->create($ip);

        $rateLimit = $limiter->consume(1);
        if (! $rateLimit->isAccepted()) {
            throw new TooManyRequestsHttpException(null, $this->translator->trans('errors.to_many_attempts'));
        }
    }
}
