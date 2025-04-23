<?php
declare(strict_types=1);

namespace Cocoon\StorageManager\Exceptions;

use Exception;
use Throwable;

/**
 * Exception pour les opérations de stockage
 *
 * Cette exception est levée lorsqu'une opération de stockage échoue.
 * Elle peut être utilisée pour encapsuler des exceptions plus spécifiques
 * et fournir des messages d'erreur plus détaillés.
 *
 * @package Cocoon\StorageManager\Exceptions
 */
class StorageOperationException extends Exception
{
    /**
     * Constructeur
     *
     * @param string $message Message d'erreur décrivant l'opération qui a échoué
     * @param int $code Code d'erreur (optionnel)
     * @param Throwable|null $previous Exception précédente pour le chaînage
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
