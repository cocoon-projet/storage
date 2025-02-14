<?php
declare(strict_types=1);

namespace Cocoon\StorageManager;

use League\Flysystem\Filesystem;
use Cocoon\StorageManager\Finder;
use Cocoon\StorageManager\FileManager;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Cocoon\StorageManager\Exceptions\StorageOperationException;

/**
 * Class Storage
 * @package Cocoon\StorageManager
 */
class Storage
{

    protected static $store;

    protected static $defaultAdater = LocalFilesystemAdapter::class;

    protected static $adapter = null;

    protected static array $adapterListing = [];

    /**
     * Initialize the storage with a base path.
     *
     * @param string $base_Path
     * @return void|bool
     */
    public static function init($base_Path)
    {
        if (is_null(static::$adapter)) {
            static::$adapter = new LocalFilesystemAdapter($base_Path);
        } else {
            return false;
        }
        static::$store = new Filesystem(static::$adapter);
    }

    /**
     * Get a file manager instance for the given path.
     *
     * @param string $path
     * @return FileManager
     */
    public static function file($path)
    {
        return new FileManager($path, static::$store);
    }

    /**
     * Get a finder instance.
     *
     * @return Finder
     */
    public static function find()
    {
        return (new Finder(static::$store));
    }

    /**
     * Get or set the adapter.
     *
     * @param mixed $adapter
     * @return mixed|null
     */
    public static function adapter($adapter = null)
    {
        if ($adapter !== null) {
            static::$adapter = $adapter;
            static::$store = new Filesystem(static::$adapter);
        }
        return static::$adapter;
    }

    /**
     * Write contents to a file.
     *
     * @param string $path
     * @param string $contents
     * @param array $config
     * @return void
     * @throws StorageOperationException
     */
    public static function put($path, $contents, $config = [])
    {
        try {
            return static::$store->write($path, $contents, $config);
        } catch (\Exception $e) {
            throw new StorageOperationException("Failed to write to path: $path", 0, $e);
        }
    }

    /**
     * Read contents from a file.
     *
     * @param string $path
     * @return string|false
     * @throws StorageOperationException
     */
    public static function get($path)
    {
        try {
            return static::$store->read($path);
        } catch (\Exception $e) {
            throw new StorageOperationException("Failed to read from path: $path", 0, $e);
        }
    }

    /**
     * Delete a file.
     *
     * @param string $path
     * @return void
     * @throws StorageOperationException
     */
    public static function delete($path)
    {
        try {
            return static::$store->delete($path);
        } catch (\Exception $e) {
            throw new StorageOperationException("Failed to delete path: $path", 0, $e);
        }
    }

    /**
     * Copy a file to a new location.
     *
     * @param string $path
     * @param string $newpath
     * @return void
     * @throws StorageOperationException
     */
    public static function copy($path, $newpath)
    {
        try {
            static::$store->copy($path, $newpath);
        } catch (\Exception $e) {
            throw new StorageOperationException("Failed to copy from $path to $newpath", 0, $e);
        }
    }

    /**
     * Move a file to a new location.
     *
     * @param string $path
     * @param string $newpath
     * @return void
     * @throws StorageOperationException
     */
    public static function move($path, $newpath)
    {
        try {
            static::$store->move($path, $newpath);
        } catch (\Exception $e) {
            throw new StorageOperationException("Failed to move from $path to $newpath", 0, $e);
        }
    }

    /**
     * Check if a file exists.
     *
     * @param string $path
     * @return bool
     * @throws StorageOperationException
     */
    public static function exists($path)
    {
        try {
            return static::$store->fileExists($path) || static::$store->directoryExists($path);
        } catch (\Exception $e) {
            throw new StorageOperationException("Failed to check existence of path: $path", 0, $e);
        }
    }

    /**
     * Create a directory.
     *
     * @param string $dirname
     * @return void
     * @throws StorageOperationException
     */
    public static function mkdir($dirname)
    {
        try {
            static::$store->createDirectory($dirname);
        } catch (\Exception $e) {
            throw new StorageOperationException("Failed to create directory: $dirname", 0, $e);
        }
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     * @return void
     * @throws StorageOperationException
     */
    public static function rmdir($dirname)
    {
        try {
            static::$store->deleteDirectory($dirname);
        } catch (\Exception $e) {
            throw new StorageOperationException("Failed to delete directory: $dirname", 0, $e);
        }
    }
}
  