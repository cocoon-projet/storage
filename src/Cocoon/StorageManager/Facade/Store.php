<?php
declare(strict_types=1);

namespace Cocoon\StorageManager\Facade;

use Cocoon\StorageManager\Storage;

/**
 * Facade pour un accès rapide aux méthodes de Storage
 *
 * Cette classe fournit une interface simplifiée pour accéder aux méthodes
 * statiques de la classe Storage. Elle permet une utilisation plus concise
 * et plus lisible du gestionnaire de stockage.
 *
 * @package Cocoon\StorageManager\Facade
 */
class Store
{
    /**
     * Gère les appels statiques dynamiques à la facade
     *
     * @param string $name Nom de la méthode à appeler
     * @param array $arguments Arguments à passer à la méthode
     * @return mixed Résultat de l'appel à la méthode
     * @throws \BadMethodCallException Si la méthode n'existe pas
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        if (!method_exists(Storage::class, $name)) {
            throw new \BadMethodCallException("La méthode $name n'existe pas dans la classe Storage");
        }

        return Storage::$name(...$arguments);
    }
}
