<?php

namespace Jasny;

/**
 * Exception that is thrown when the configuration is invalid or can't be loaded.
 * 
 * Note: This is a runtime exception. It is not thrown if a loader is used incorrectly.
 */
class ConfigException extends \RuntimeException
{
}
