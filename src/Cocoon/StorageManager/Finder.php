<?php


namespace Cocoon\StorageManager;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use Cocoon\StorageManager\Filter\FilterPathCollection;
use Cocoon\StorageManager\Exceptions\StorageOperationException;

/**
 * Class Finder
 * @package Cocoon\StorageManager
 */
class Finder implements IteratorAggregate, Countable
{
    protected $only = [];
    protected $except = [];
    protected $size = [];
    protected $date = [];
    protected $files = false;
    protected $folders = false;
    protected $filesystem;
    public $collection = [];

    /**
     * Finder constructor.
     * @param $fileSystem
     */
    public function __construct($fileSystem)
    {
        $this->filesystem = $fileSystem;
    }

    /**
     * Set the finder to only search for files.
     *
     * @return Finder
     */
    public function files(): Finder
    {
        $this->files = true;
        return $this;
    }

    /**
     * Set the finder to only search for folders.
     *
     * @return Finder
     */
    public function folders(): Finder
    {
        $this->folders = true;
        return $this;
    }

    /**
     * Set the file extensions to include in the search.
     *
     * @param string|array $extension
     * @return Finder
     */
    public function only($extension): Finder
    {
        $this->only = (array) $extension;
        return $this;
    }

    /**
     * Set the file extensions to exclude from the search.
     *
     * @param string|array $extension
     * @return Finder
     */
    public function except($extension): Finder
    {
        $this->except = (array) $extension;
        return $this;
    }

    /**
     * Set the file sizes to include in the search.
     *
     * @param array $size
     * @return Finder
     */
    public function size($size): Finder
    {
        $this->size = (array) $size;
        return $this;
    }

    /**
     * Set the file dates to include in the search.
     *
     * @param array $date
     * @return Finder
     */
    public function date($date): Finder
    {
        $this->date = (array) $date;
        return $this;
    }

    /**
     * Set the path to search in.
     *
     * @param string $path
     * @param bool $recursive
     * @return Finder
     * @throws StorageOperationException
     */
    public function in($path, $recursive = false): Finder
    {
        try {
            $collect = $this->filesystem->listContents($path, $recursive);
        } catch (\Exception $e) {
            throw new StorageOperationException("Failed to list contents for path: $path", 0, $e);
        }

        foreach ($collect as $item) {
            $this->collection[] = new FileInfo($item);
        }

        $filter = new FilterPathCollection($this);

        if ($this->files) {
            $filter->filesFilter();
        }
        if ($this->folders) {
            $filter->foldersFilter();
        }
        if (!empty($this->only)) {
            $filter->onlyFilter($this->only);
        }
        if (!empty($this->except)) {
            $filter->exceptFilter($this->except);
        }
        if (!empty($this->date)) {
            $filter->dateFilter($this->date);
        }
        if (!empty($this->size)) {
            $filter->sizeFilter($this->size);
        }

        return $this;
    }

    /**
     * Check if the finder has results.
     *
     * @return bool
     */
    public function hasResults(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Get an iterator for the collection.
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->collection);
    }

    /**
     * Get the count of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * Get the collection as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->collection;
    }

    /**
     * Dump the collection.
     *
     * @return void
     */
    public function dump()
    {
        if(function_exists('dump')) {
            return dump($this->collection);
        }
        return var_dump($this->collection);
    }
}
