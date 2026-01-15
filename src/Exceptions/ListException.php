<?php

declare(strict_types=1);

namespace Blamodex\Lists\Exceptions;

use Exception;

/**
 * Base exception class for Laravel Lists package.
 *
 * All domain-specific exceptions in this package extend this class,
 * allowing consumers to catch all list-related exceptions in one catch block.
 */
class ListException extends Exception
{
}
