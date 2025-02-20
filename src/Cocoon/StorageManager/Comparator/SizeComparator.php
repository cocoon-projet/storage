<?php


namespace Cocoon\StorageManager\Comparator;

use Cocoon\StorageManager\Finder;
use InvalidArgumentException;

/**
 * Class SizeComparator
 * @package Cocoon\StorageManager\Comparator
 */
class SizeComparator
{
    /**
     * @var Finder
     */
    private $finder ;

    /**
     * SizeComparator constructor.
     * @param Finder $finder
     */
    public function __construct(Finder $finder)
    {
        $this->finder= $finder;
    }

    public function filterSizeComparison($size)
    {
        $this->finder->collection = array_filter($this->finder->collection, function ($find) use ($size) {
            return $this->sizeComparison($find->size(), $size);
        });
    }

    /**
     * operator: >, >=, <, <=, ==, !=
     *
     * @param $size_file
     * @param string $size
     * @return bool
     */
    private function sizeComparison($size_file, string $size): bool
    {
        $k = 1000; // ko
        $m = 1000000; // mo
        $g = 1000000000; //go

        if (!preg_match('#(>|>=|<|<=|==|!=)\s([0-9]+)([kmg])?#', $size, $matches)) {
            throw new InvalidArgumentException('la valeur ' . $size . ' n\est pas valide');
        }
        $fileSize = null;
        if (isset($matches[3])) {
            if ($matches[3] == 'k') {
                $fileSize = $matches[2] * $k;
            } elseif ($matches[3] == 'm') {
                $fileSize = $matches[2] * $m;
            } elseif ($matches[3] == 'g') {
                $fileSize = $matches[2] * $g;
            }
        } else {
            $fileSize = $matches[2];
        }

        switch ($matches[1]) {
            case '>':
                return $size_file > $fileSize;
            case '>=':
                return $size_file >= $fileSize;
            case '==':
                return $size_file == $fileSize;
            case '<=':
                return $size_file <= $fileSize;
            case '!=':
                return $size_file != $fileSize;
            case '<':
                return $size_file < $fileSize;
        }
    }
}
