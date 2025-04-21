<?php
declare(strict_types=1);

namespace Cocoon\StorageManager\Exceptions;

use Exception;
use Throwable;

/**
 * Exception pour les erreurs de validation
 * 
 * Cette exception est levée lorsqu'une validation échoue, par exemple
 * lors de la vérification des paramètres d'une opération de stockage
 * ou lors de la validation des expressions de filtrage.
 * 
 * @package Cocoon\StorageManager\Exceptions
 */
class ValidationException extends Exception
{
    /**
     * Constructeur
     * 
     * @param string $message Message d'erreur décrivant la validation qui a échoué
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