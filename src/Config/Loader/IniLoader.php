<?php

namespace Jasny\Config\Loader;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\Loader\AbstractFileLoader;

/**
 * Load and parse .ini config files from a directory.
 */
class IniLoader extends AbstractFileLoader
{
    /**
     * Parse ini file
     *
     * @param string $file    Filename
     * @param array  $options
     * @return array
     */
    protected function loadFile(string $file, array $options)
    {
        return parse_ini_file(
            $file,
            $options['process_sections'] ?? true,
            $options['mode'] ?? INI_SCANNER_NORMAL
        );
    }
}
