<?php
declare(strict_types=1);

namespace Cocoon\StorageManager;

use League\Flysystem\FilesystemInterface;

class Folder
{
    protected $dir;
    protected $path;

    public function __construct(FilesystemInterface $fileSystem, $path)
    {
        $this->path = $path;
        $this->dir = $fileSystem;
    }
}
