<?php

declare(strict_types=1);

namespace Cocoon\StorageManager\Comparator;

use Cocoon\StorageManager\Finder;
use Cocoon\StorageManager\Exceptions\ValidationException;

/**
 * Comparateur de dates pour le filtrage des fichiers
 *
 * Cette classe permet de comparer les dates de modification des fichiers
 * selon différents critères et opérateurs. Elle supporte les comparaisons
 * avec des dates spécifiques ou des périodes relatives.
 *
 * Opérateurs supportés :
 * - >, >=, <, <=, == : comparaisons classiques
 * - last : fichiers modifiés après une période relative
 * - after : synonyme de >
 * - before : synonyme de <
 *
 * Périodes relatives supportées :
 * - day, week, month, year
 *
 * @package Cocoon\StorageManager\Comparator
 */
class DateComparator
{
    /** @var Finder Instance du moteur de recherche */
    private Finder $finder;

    /** @var array Liste des opérateurs valides */
    private const OPERATORS = ['>', '>=', '<', '<=', '==', 'last', 'after', 'before'];

    /** @var string Pattern de validation des expressions de date */
    private const DATE_PATTERN =
    '#(>|>=|<|<=|==|last|after|before)\s*([0-9]{4}-[0-9]{2}-[0-9]{2}|[0-9]+\s*(minute|hour|day|week|month|year)s?)?#';

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
     * Filtre la collection selon le critère de date
     *
     * @param string $date Expression de date à comparer
     * @return void
     * @throws ValidationException Si l'expression de date est invalide
     */
    public function filterDateComparison(string $date): void
    {
        $this->finder->collection = array_filter(
            $this->finder->collection,
            fn($find) => $this->dateComparison($find->lastModified(), $date)
        );
    }

    /**
     * Compare les dates selon l'expression donnée
     *
     * @param string $date_expression Expression de date
     * @param int $file_date Timestamp du fichier
     * @return bool Résultat de la comparaison
     * @throws ValidationException Si l'expression est invalide
     */
    private function dateComparison(int $file_date, string $date_expression): bool
    {
        if (!preg_match(self::DATE_PATTERN, $date_expression, $matches)) {
            throw new ValidationException("Expression de date invalide : $date_expression");
        }

        if (!in_array($matches[1], self::OPERATORS, true)) {
            throw new ValidationException("L'opérateur ({$matches[1]}) n'est pas valide");
        }

        $operator = match ($matches[1]) {
            'after', 'last' => '>',
            'before' => '<',
            default => $matches[1]
        };

        // Gestion des expressions de temps relatives
        if (isset($matches[2]) &&
        preg_match('#([0-9]+)\s*(minute|hour|day|week|month|year)s?#', $matches[2], $time_matches)) {
            $value = (int)$time_matches[1];
            $unit = $time_matches[2];
            
            // Calcul de la date de référence en soustrayant le temps spécifié
            $reference_time = match ($unit) {
                'minute' => strtotime("-$value minutes"),
                'hour' => strtotime("-$value hours"),
                'day' => strtotime("-$value days"),
                'week' => strtotime("-$value weeks"),
                'month' => strtotime("-$value months"),
                'year' => strtotime("-$value years"),
                default => time()
            };
            
            // Comparaison avec la date de référence
            return match ($operator) {
                '>' => $file_date > $reference_time,
                '>=' => $file_date >= $reference_time,
                '<' => $file_date < $reference_time,
                '<=' => $file_date <= $reference_time,
                '==' => $file_date == $reference_time,
                default => false
            };
        }

        // Pour les dates absolues
        $compare = strtotime($matches[2] ?? 'now');
        if ($compare === false) {
            throw new ValidationException("Date invalide dans l'expression : $date_expression");
        }

        return match ($operator) {
            '>' => $file_date > $compare,
            '>=' => $file_date >= $compare,
            '<' => $file_date < $compare,
            '<=' => $file_date <= $compare,
            '==' => $file_date == $compare,
            default => false
        };
    }
}
