<?php
declare(strict_types=1);

namespace Cocoon\StorageManager;

use Carbon\Carbon;
use DateTimeInterface;



class FileManager
{
    protected $file;
    protected $path;

    public function __construct($adapter, $path)
    {
        $this->file = $adapter;
        $this->path = $path;
    }

    public function getPath() :string
    {
        return $this->path;
    }

    public function put($content, array $config = [])
    {
        $this->file->write($this->getPath(), $content, $config);
   
    }
    public function get()
    {
        return $this->file->read($this->getPath());
    }

    public function delete()
    {
        $this->file->delete($this->getPath());
    }   

    public function exists(): bool
    {
        return $this->file->fileExists($this->getPath());
    }

    public function move(string $source, string $destination, array $config = [])
    {
        $this->file->move($source, $destination,$config);
    }

    public function copy(string $source, string $destination, array $config = [])
    {
        $this->file->copy($source, $destination, $config);
    }

    public function lastModified(): int
    {
        return $this->file->lastModified($this->getPath());
    }

    public function size(): int
    {
        return $this->file->fileSize($this->getPath());
    }

    public function type(): string
    {
        return $this->file->mimeType($this->getPath());
    }

    public function setVisibility(string $visibility)
    {
        $this->file->setVisibility($this->getPath(), $visibility);
    }

    public function visibility(): string
    {
        return $this->file->visibility($this->getPath());
    }

    public function publicUrl(array $config = []): string
    {
        return $this->file->publicUrl($this->getPath(), $config);
    }

    public function temporaryUrl(int $expiration, DateTimeInterface $expiresAt, array $config = []): string
    {
        return $this->file->temporaryUrl($this->getPath(), $expiresAt, $config);
    }

    public function checksum(string $path, array $config = []): string
    {
        return $this->file->checksum($path, $config);
    }
 

    public function dateTime(string $locale = 'fr'): Carbon
    {
        carbon::setLocale($locale);
        return Carbon::createFromTimestamp($this->file->lastModified());
    }

}
