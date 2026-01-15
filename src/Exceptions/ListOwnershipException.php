<?php

declare(strict_types=1);

namespace Blamodex\Lists\Exceptions;

/**
 * Exception thrown when a list ownership validation fails.
 *
 * This exception is thrown when attempting to update or delete a list
 * that is not owned by the current model.
 */
class ListOwnershipException extends ListException
{
    /**
     * Create a new list ownership exception.
     */
    public static function notOwner(): self
    {
        return new self('The provided list is not owned by this model.');
    }
}
