<?php
declare(strict_types=1);


namespace App\User\Profile\Photo;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GetFileService
{
    public function __construct(
        #[Autowire('@default.storage')]
        private FilesystemOperator $storage,
    ) {

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
