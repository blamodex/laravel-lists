<?php

declare(strict_types=1);

namespace Blamodex\Lists\Exceptions;

/**
 * Exception thrown when a listable model is invalid.
 *
 * This exception is thrown when attempting to add an unsaved model
 * to a list, as list items require the model to have a primary key.
 */
class InvalidListableException extends ListException
{
    /**
     * Create exception for an unsaved listable model.
     */
    public static function unsavedModel(): self
    {
        return new self('The listable model must be saved before adding to a list.');
    }
}
