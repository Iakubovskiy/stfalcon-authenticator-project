<?php
declare(strict_types=1);


namespace App\User\Profile\Photo;

use DateInterval;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class GetPhotoForTwig implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ClockInterface $clock,
        private readonly UriSigner $uriSigner,
        private readonly UrlGeneratorInterface $urlGenerator,
    )
    {

    }
    public function getPhotoUrl(): string
    {
        $expireAt = $this->clock->now()->add(new DateInterval('PT10M'))->getTimestamp();
        $photoRawUrl = $this->urlGenerator->generate(
            'user_photo',
            [
                'eat' => $expireAt,
            ],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->uriSigner->sign($photoRawUrl);
    }
}
