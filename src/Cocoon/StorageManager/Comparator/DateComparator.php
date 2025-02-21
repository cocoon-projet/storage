<?php


namespace Cocoon\StorageManager\Comparator;

use Cocoon\StorageManager\Finder;
use InvalidArgumentException;

class DateComparator
{
    /**
     * @var Finder
     */
    private $finder ;

    /**
     * DateComparator constructor.
     * @param Finder $finder
     */
    public function __construct(Finder $finder)
    {
        $this->finder= $finder;
    }

    public function filterDateComparison($date)
    {
        $this->finder->collection = array_filter($this->finder->collection, function ($find) use ($date) {
            return $this->dateComparison($find->lastModified(), $date);
        });
    }
    // TODO datetime::format Y-m-d
    public function dateComparison($file_date, $date): bool
    {
        if (!preg_match(
            '#(>|>=|<|<=|==|last|after|before)\s([0-9]{4}-[0-9]{2}-[0-9]{2}|day|week|month|year)?#',
            $date,
            $matches
        )) {
            throw new InvalidArgumentException('la valeur (' . $date . ') n\est pas valide');
        }
        $before = '<';
        $after = '>';
        //var_dump($matches);
        $compare = null;
        $operators = ['>','>=','<','<=','==','last','after','before'];
        if (in_array($matches[1], $operators)) {
            if ($matches[1] == 'after' or $matches[1] == 'last') {
                if ($matches[1] == 'last') {
                    $compare = strtotime($matches[0]);
                }
                $operator = $after;
            } elseif ($matches[1] == 'before') {
                $operator = $before;
                $compare = strtotime($matches[2]);
            } else {
                $operator = $matches[1];
                $compare = strtotime($matches[2]);
            }
        } else {
            throw new InvalidArgumentException('la valeur (' . $matches[1] . ') n\'est pas valide un opérateur valide');
        }
        switch ($operator) {
            case '>':
                return $file_date > $compare;
            case '>=':
                return $file_date >= $compare;
            case '==':
                return $file_date == $compare;
            case '<=':
                return $file_date <= $compare;
            case '<':
                return $file_date < $compare;
        }
    }
}
