<?php


namespace Cocoon\StorageManager\Filter;

use Cocoon\StorageManager\Comparator\DateComparator;
use Cocoon\StorageManager\Comparator\SizeComparator;
use Cocoon\StorageManager\Finder;

class FilterPathCollection
{
    protected $finder;

    public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }

    public function foldersFilter()
    {
        $this->finder->collection = array_filter($this->finder->collection, function ($find) {
            return $find->type() == 'dir';
        });
    }

    public function filesFilter()
    {
        $this->finder->collection = array_filter($this->finder->collection, function ($find) {
            return $find->type() == 'file';
        });
    }
    public function sizeFilter($size)
    {
        $comparator = new SizeComparator($this->finder);
        if (is_array($size)) {
            foreach ($size as $item) {
                $comparator->filterSizeComparison($item);
            }
        }
    }

    public function dateFilter($date)
    {
        $comparator = new DateComparator($this->finder);
        if (is_array($date)) {
            foreach ($date as $item) {
                $comparator->filterDateComparison($item);
            }
        }
    }

    public function onlyFilter($extension)
    {
        $this->finder->collection = array_filter($this->finder->collection, function ($find) use ($extension) {
                return in_array($find->extension(), $extension);
        });
    }

    public function exceptFilter($extension)
    {
        $this->finder->collection = array_filter($this->finder->collection, function ($find) use ($extension) {
                return !in_array($find->extension(), $extension);
        });
    }
}
