<?php

declare (strict_types = 1);

namespace Jasny\Config\Exception;

use RuntimeException;
use Jasny\Config\ConfigExceptionInterface;

/**
 * Failed to load config
 */
class LoadException extends RuntimeException implements ConfigExceptionInterface
{
}
