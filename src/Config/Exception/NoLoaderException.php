<?php

namespace Jasny\Config\Exception;

use OutOfBoundsException;
use Jasny\Config\ConfigExceptionInterface;

/**
 * No loader found for specific file or object type
 */
class NoLoaderException extends OutOfBoundsException implements ConfigExceptionInterface
{
}
