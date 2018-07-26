<?php

declare(strict_types=1);

namespace Jasny\Config;

use Jasny\Config;

/**
 * Config loader
 */
interface LoaderInterface
{
    /**
     * Load configuration settings
     * 
     * @param mixed $source
     * @param array $options
     * @return Config
     */
    public function load($source, array $options = []);
}
