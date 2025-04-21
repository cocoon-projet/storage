<?php

declare(strict_types=1);

namespace Cocoon\StorageManager\Filter;

use Cocoon\StorageManager\Comparator\DateComparator;
use Cocoon\StorageManager\Comparator\SizeComparator;
use Cocoon\StorageManager\Finder;

/**
 * Classe de filtrage des collections de fichiers
 * 
 * Cette classe permet d'appliquer différents filtres sur une collection de fichiers
 * et répertoires. Elle utilise des comparateurs spécialisés pour les filtres
 * complexes comme la taille et la date.
 * 
 * Fonctionnalités principales :
 * - Filtrage par type (fichiers/répertoires)
 * - Filtrage par extension
 * - Filtrage par taille
 * - Filtrage par date
 * 
 * @package Cocoon\StorageManager\Filter
 */
class FilterPathCollection
{
    /** @var Finder Instance du moteur de recherche */
    protected Finder $finder;

    /**
     * Constructeur
     * 
     * @param Finder $finder Instance du moteur de recherche
     */
    public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * Filtre la collection pour ne garder que les répertoires
     * 
     * @return void
     */
    public function foldersFilter(): void
    {
        $this->finder->collection = array_filter($this->finder->collection, function ($find) {
            return $find->type() === 'dir';
        });
    }

    /**
     * Filtre la collection pour ne garder que les fichiers
     * 
     * @return void
     */
    public function filesFilter(): void
    {
        $this->finder->collection = array_filter($this->finder->collection, function ($find) {
            return $find->type() === 'file';
        });
    }

    /**
     * Filtre la collection selon les critères de taille
     * 
     * @param array|string $size Critère(s) de taille
     * @return void
     */
    public function sizeFilter(array|string $size): void
    {
        $comparator = new SizeComparator($this->finder);
        if (is_array($size)) {
            foreach ($size as $item) {
                $comparator->filterSizeComparison($item);
            }
        } else {
            $comparator->filterSizeComparison($size);
        }
    }

    /**
     * Filtre la collection selon les critères de date
     * 
     * @param array|string $date Critère(s) de date
     * @return void
     */
    public function dateFilter(array|string $date): void
    {
        $comparator = new DateComparator($this->finder);
        if (is_array($date)) {
            foreach ($date as $item) {
                $comparator->filterDateComparison($item);
            }
        } else {
            $comparator->filterDateComparison($date);
        }
    }

    /**
     * Filtre la collection pour ne garder que les fichiers avec les extensions spécifiées
     * 
     * @param array $extension Liste des extensions à inclure
     * @return void
     */
    public function onlyFilter(array $extension): void
    {
        $this->finder->collection = array_filter($this->finder->collection, function ($find) use ($extension) {
            return in_array($find->extension(), $extension, true);
        });
    }

    /**
     * Filtre la collection pour exclure les fichiers avec les extensions spécifiées
     * 
     * @param array $extension Liste des extensions à exclure
     * @return void
     */
    public function exceptFilter(array $extension): void
    {
        $this->finder->collection = array_filter($this->finder->collection, function ($find) use ($extension) {
            return !in_array($find->extension(), $extension, true);
        });
    }
}
