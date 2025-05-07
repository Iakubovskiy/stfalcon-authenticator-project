<?php

declare(strict_types=1);

namespace App\User\Profile\UseCases\Edit;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileService
{
    public function __construct(
        #[Autowire('@default.storage')]
        private FilesystemOperator $storage,
    ) {

    }

    public function saveFile(UploadedFile $file): string
    {
        $originalFilename = $file->getClientOriginalName();
        $savedFilename = uniqid('', true). '-'. $originalFilename;
        $stream = fopen($file->getRealPath(), 'rb');
        $this->storage->writeStream(
            $savedFilename,
            $stream
        );
        if (is_resource($stream)) {
            fclose($stream);
        }
        return $savedFilename;
    }
}
