<?php

declare(strict_types=1);

namespace Cocoon\StorageManager\Comparator;

use Cocoon\StorageManager\Finder;
use Cocoon\StorageManager\Exceptions\ValidationException;

/**
 * Comparateur de taille pour le filtrage des fichiers
 * 
 * Cette classe permet de comparer les tailles des fichiers selon différents
 * critères et opérateurs. Elle supporte les comparaisons avec des tailles
 * spécifiques ou des expressions de taille.
 * 
 * Opérateurs supportés :
 * - >, >=, <, <=, ==, != : comparaisons classiques
 * 
 * Unités supportées :
 * - k, kb, kilo : kilo-octets (1024 octets)
 * - m, mb, mega : méga-octets (1024² octets)
 * - g, gb, giga : giga-octets (1024³ octets)
 * 
 * @package Cocoon\StorageManager\Comparator
 */
class SizeComparator
{
    /** @var Finder Instance du moteur de recherche */
    private Finder $finder;

    /** @var string Pattern de validation des expressions de taille */
    private const SIZE_PATTERN = '#(>|>=|<|<=|==|!=)\s*([0-9]+)\s*(k|kb|kilo|m|mb|mega|g|gb|giga)?#i';

    /** @var array Multiplicateurs pour les unités de taille */
    private const SIZE_MULTIPLIERS = [
        'k' => 1024,
        'kb' => 1024,
        'kilo' => 1024,
        'm' => 1024 * 1024,
        'mb' => 1024 * 1024,
        'mega' => 1024 * 1024,
        'g' => 1024 * 1024 * 1024,
        'gb' => 1024 * 1024 * 1024,
        'giga' => 1024 * 1024 * 1024
    ];

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
     * Filtre la collection selon le critère de taille
     * 
     * @param string $size Expression de taille à comparer
     * @return void
     * @throws ValidationException Si l'expression de taille est invalide
     */
    public function filterSizeComparison(string $size): void
    {
        $this->finder->collection = array_filter(
            $this->finder->collection,
            fn($find) => $this->sizeComparison($find->size(), $size)
        );
    }

    /**
     * Compare une taille de fichier avec une expression de taille
     * 
     * @param int $file_size Taille du fichier en octets
     * @param string $size Expression de taille à comparer
     * @return bool True si la comparaison est valide
     * @throws ValidationException Si l'expression de taille est invalide
     */
    public function sizeComparison(int $file_size, string $size): bool
    {
        if (!preg_match(self::SIZE_PATTERN, $size, $matches)) {
            throw new ValidationException("L'expression de taille ($size) n'est pas valide");
        }

        $operator = $matches[1];
        $value = (int) $matches[2];
        $unit = strtolower($matches[3] ?? '');

        if ($unit !== '' && !isset(self::SIZE_MULTIPLIERS[$unit])) {
            throw new ValidationException("L'unité de taille ($unit) n'est pas valide");
        }

        $compare = $unit !== '' ? $value * self::SIZE_MULTIPLIERS[$unit] : $value;

        return match ($operator) {
            '>' => $file_size > $compare,
            '>=' => $file_size >= $compare,
            '==' => $file_size === $compare,
            '!=' => $file_size !== $compare,
            '<=' => $file_size <= $compare,
            '<' => $file_size < $compare,
            default => throw new ValidationException("Opérateur invalide: $operator")
        };
    }
}
