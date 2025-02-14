<?php
declare(strict_types=1);

namespace Cocoon\StorageManager\Facade;

use Cocoon\StorageManager\Storage;

class Store
{
    public static function __callStatic($name, $arguments)
    {
        $instance = Storage::class;
        return $instance->$name(...$arguments);
    }
}
