<?php

declare (strict_types = 1);

namespace Jasny\Config\Exception;

use OutOfBoundsException;
use Jasny\Config\ConfigExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Config source can't be found
 */
class NotFoundException extends OutOfBoundsException implements ConfigExceptionInterface, NotFoundExceptionInterface
{
}
