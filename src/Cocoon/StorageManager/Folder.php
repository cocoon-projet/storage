<?php
declare(strict_types=1);

namespace Cocoon\StorageManager;

use League\Flysystem\Filesystem;

class Folder
{
    protected Filesystem $dir;
    protected string $path;

    public function __construct(Filesystem $fileSystem, string $path)
    {
        $this->path = $path;
        $this->dir = $fileSystem;
    }
}
