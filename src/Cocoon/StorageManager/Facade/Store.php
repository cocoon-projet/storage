<?php
declare(strict_types=1);

namespace Cocoon\StorageManager\Facade;

use Cocoon\StorageManager\Storage;

/**
 * Facade pour un accès rapide aux méthodes de Storage
 */
class Store
{
    /**
     * Handle dynamic static calls to the facade.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return Storage::$name(...$arguments);
    }
}
