<?php

declare(strict_types=1);

namespace Cocoon\StorageManager;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use League\Flysystem\Filesystem;
use League\Flysystem\FileAttributes;
use League\Flysystem\DirectoryAttributes;
use Cocoon\StorageManager\Filter\FilterPathCollection;
use Cocoon\StorageManager\Exceptions\StorageOperationException;
use Cocoon\StorageManager\Comparator\DateComparator;
use Cocoon\StorageManager\Comparator\SizeComparator;
use Cocoon\StorageManager\Exceptions\ValidationException;

/**
 * Moteur de recherche de fichiers
 * 
 * Cette classe permet de rechercher des fichiers selon différents critères
 * et de les filtrer selon leur taille, date, extension, etc.
 * 
 * Fonctionnalités principales :
 * - Recherche de fichiers et répertoires
 * - Filtrage par taille, date, extension
 * - Tri par différents critères
 * - Support des expressions de recherche avancées
 * 
 * @package Cocoon\StorageManager
 */
class Finder implements IteratorAggregate, Countable
{
    /** @var Filesystem Instance du système de fichiers */
    private Filesystem $filesystem;

    /** @var array Collection de fichiers trouvés */
    public array $collection = [];

    /** @var array Filtres à appliquer */
    private array $filters = [];

    /** @var array Extensions à inclure */
    private array $only = [];

    /** @var array Extensions à exclure */
    private array $except = [];

    /** @var array Critères de taille */
    private array $size = [];

    /** @var array Critères de date */
    private array $date = [];

    /** @var string Chemin de recherche */
    private string $path = '';

    /**
     * Constructeur
     * 
     * @param Filesystem $filesystem Instance du système de fichiers
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Définit le chemin de recherche
     * 
     * @param string $path Chemin à rechercher
     * @return $this Instance courante pour le chaînage
     */
    public function in(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Recherche uniquement les fichiers
     * 
     * @return $this Instance courante pour le chaînage
     */
    public function files(): self
    {
        $this->filters['type'] = 'file';
        return $this;
    }

    /**
     * Recherche uniquement les répertoires
     * 
     * @return $this Instance courante pour le chaînage
     */
    public function directories(): self
    {
        $this->filters['type'] = 'dir';
        return $this;
    }

    /**
     * Filtre par extensions
     * 
     * @param string|array $extensions Extension(s) à inclure
     * @return $this Instance courante pour le chaînage
     */
    public function only(string|array $extensions): self
    {
        $this->only = is_array($extensions) ? $extensions : [$extensions];
        return $this;
    }

    /**
     * Exclut des extensions
     * 
     * @param string|array $extensions Extension(s) à exclure
     * @return $this Instance courante pour le chaînage
     */
    public function except(string|array $extensions): self
    {
        $this->except = is_array($extensions) ? $extensions : [$extensions];
        return $this;
    }

    /**
     * Filtre par taille
     * 
     * @param string|array $size Expression(s) de taille
     * @return $this Instance courante pour le chaînage
     * @throws ValidationException Si l'expression de taille est invalide
     */
    public function size(string|array $size): self
    {
        $this->size = is_array($size) ? $size : [$size];
        return $this;
    }

    /**
     * Filtre par date
     * 
     * @param string|array $date Expression(s) de date
     * @return $this Instance courante pour le chaînage
     * @throws ValidationException Si l'expression de date est invalide
     */
    public function date(string|array $date): self
    {
        $this->date = is_array($date) ? $date : [$date];
        return $this;
    }

    /**
     * Exécute la recherche
     * 
     * @return array Liste des fichiers trouvés
     * @throws ValidationException En cas d'erreur de validation
     */
    public function get(): array
    {
        $this->collection = $this->listContents($this->path);
        $this->applyFilters();
        return $this->collection;
    }

    /**
     * Liste le contenu d'un répertoire de manière récursive
     * 
     * @param string $path Chemin à lister
     * @return array Liste des fichiers et répertoires
     */
    private function listContents(string $path): array
    {
        $contents = $this->filesystem->listContents($path, true)->toArray();
        $result = [];

        foreach ($contents as $item) {
            if ($this->matchesFilters($item)) {
                if (isset($this->filters['type'])) {
                    if ($this->filters['type'] === 'file' && $item instanceof FileAttributes) {
                        $result[] = new FileManager($item->path(), $this->filesystem);
                    } elseif ($this->filters['type'] === 'dir' && $item instanceof DirectoryAttributes) {
                        $result[] = new FileManager($item->path(), $this->filesystem);
                    }
                } else {
                    $result[] = new FileManager($item->path(), $this->filesystem);
                }
            }
        }

        return $result;
    }

    /**
     * Vérifie si un élément correspond aux filtres
     * 
     * @param FileAttributes|DirectoryAttributes $item Élément à vérifier
     * @return bool True si l'élément correspond aux filtres
     */
    private function matchesFilters(FileAttributes|DirectoryAttributes $item): bool
    {
        if (isset($this->filters['type'])) {
            if ($this->filters['type'] === 'file' && !($item instanceof FileAttributes)) {
                return false;
            }
            if ($this->filters['type'] === 'dir' && !($item instanceof DirectoryAttributes)) {
                return false;
            }
        }

        if (!empty($this->only) && $item instanceof FileAttributes) {
            $extension = pathinfo($item->path(), PATHINFO_EXTENSION);
            if (!in_array($extension, $this->only)) {
                return false;
            }
        }

        if (!empty($this->except) && $item instanceof FileAttributes) {
            $extension = pathinfo($item->path(), PATHINFO_EXTENSION);
            if (in_array($extension, $this->except)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Applique les filtres à la collection
     * 
     * @return void
     * @throws ValidationException En cas d'erreur de validation
     */
    private function applyFilters(): void
    {
        if (!empty($this->size)) {
            $comparator = new SizeComparator($this);
            foreach ($this->size as $size) {
                $comparator->filterSizeComparison($size);
            }
        }

        if (!empty($this->date)) {
            $comparator = new DateComparator($this);
            foreach ($this->date as $date) {
                $comparator->filterDateComparison($date);
            }
        }
    }

    /**
     * Trie la collection par date de modification
     * 
     * @param bool $descending True pour trier par ordre décroissant
     * @return $this Instance courante pour le chaînage
     */
    public function sortByDate(bool $descending = false): self
    {
        usort($this->collection, function ($a, $b) use ($descending) {
            $result = $a->lastModified() <=> $b->lastModified();
            return $descending ? -$result : $result;
        });
        return $this;
    }

    /**
     * Trie la collection par taille
     * 
     * @param bool $descending True pour trier par ordre décroissant
     * @return $this Instance courante pour le chaînage
     */
    public function sortBySize(bool $descending = false): self
    {
        usort($this->collection, function ($a, $b) use ($descending) {
            $result = $a->size() <=> $b->size();
            return $descending ? -$result : $result;
        });
        return $this;
    }

    /**
     * Trie la collection par extension
     * 
     * @param bool $descending True pour trier par ordre décroissant
     * @return $this Instance courante pour le chaînage
     */
    public function sortByExtension(bool $descending = false): self
    {
        usort($this->collection, function ($a, $b) use ($descending) {
            $result = strcasecmp(
                pathinfo($a->getPath(), PATHINFO_EXTENSION),
                pathinfo($b->getPath(), PATHINFO_EXTENSION)
            );
            return $descending ? -$result : $result;
        });
        return $this;
    }

    /**
     * Trie la collection par nom
     * 
     * @param bool $descending True pour trier par ordre décroissant
     * @return $this Instance courante pour le chaînage
     */
    public function sortByName(bool $descending = false): self
    {
        usort($this->collection, function ($a, $b) use ($descending) {
            $result = strcasecmp($a->getPath(), $b->getPath());
            return $descending ? -$result : $result;
        });
        return $this;
    }

    /**
     * Vérifie si des résultats ont été trouvés
     * 
     * @return bool True si des résultats existent
     */
    public function hasResults(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Obtient un itérateur pour la collection
     * 
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->collection);
    }

    /**
     * Obtient le nombre d'éléments dans la collection
     * 
     * @return int Nombre d'éléments
     */
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * Convertit la collection en tableau
     * 
     * @return array Tableau des résultats
     */
    public function toArray(): array
    {
        return $this->collection instanceof \Traversable
            ? iterator_to_array($this->collection, false)
            : (array) $this->collection;
    }

    /**
     * Affiche le contenu de la collection (débogage)
     * 
     * @return void
     */
    public function dump(): void
    {
        if (function_exists('dump')) {
            dump($this->collection);
            return;
        }
        var_dump($this->collection);
        exit(1);
    }
}
