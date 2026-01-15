<?php

declare(strict_types=1);

namespace Blamodex\Lists\Exceptions;

/**
 * Exception thrown when required list attributes are missing or invalid.
 *
 * This exception is thrown when creating a list without required attributes
 * such as the "name" attribute.
 */
class InvalidListAttributeException extends ListException
{
    /**
     * Create exception for a missing required attribute.
     */
    public static function missingAttribute(string $attribute): self
    {
        return new self(sprintf('The "%s" attribute is required to create a list.', $attribute));
    }

    /**
     * Create exception for an empty required attribute.
     */
    public static function emptyAttribute(string $attribute): self
    {
        return new self(sprintf('The "%s" attribute cannot be empty.', $attribute));
    }
}
