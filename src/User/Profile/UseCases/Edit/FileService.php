<?php

declare(strict_types=1);

namespace App\User\Profile\UseCases\Edit;

use Carbon\Carbon;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function getFile(string $name): ?StreamedResponse
    {
        if(! $this->storage->fileExists($name)){
            return null;
        }
        $stream = $this->storage->readStream($name);

        return new StreamedResponse(function () use ($stream){
            fpassthru($stream);
            fclose($stream);
        });
    }
}
