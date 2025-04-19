<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService
{
    public function __construct(
        #[Autowire(param: 'profile_pictures_directory')]
        private readonly string $rootDirectory
    ) {

    }

    public function saveFile(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $savedFilename = $originalFilename . '-' . uniqid() . '.' . $file->guessExtension();
        $file->move(
            $this->rootDirectory,
            $savedFilename
        );
        return $savedFilename;
    }
}
