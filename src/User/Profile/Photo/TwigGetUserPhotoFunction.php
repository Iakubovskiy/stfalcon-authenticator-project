<?php
declare(strict_types=1);


namespace App\User\Profile\Photo;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigGetUserPhotoFunction extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getPhotoUrl', [GetPhotoForTwig::class, 'getPhotoUrl']),
        ];
    }


}
