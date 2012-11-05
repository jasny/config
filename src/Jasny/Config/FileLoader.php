<?php

namespace Jasny\Config;

use Jasny\Config;

require_once 'Loader.php';

/**
 * Loader for config files.
 * 
 * @package Config
 */
class FileLoader implements Loader
{
    /**
     * Load a config file
     * 
     * @param string $file     Filename
     * @param array  $options
     * @return object
     */
    public function load($file, $options=array())
    {
        if (!file_exists($file)) {
            if (empty($options['optional'])) trigger_error("Unable to load config file '$file'", E_USER_WARNING);
            return null;
        }
        
        $loader = isset($options['loader']) ? $options['loader'] : pathinfo($file, PATHINFO_EXTENSION);
        if (!isset(Config::$loaders[$loader])) throw new \Exception("Don't know how to parse config file '{$file}'.", E_WARNING);

        $class = Config::$loaders[$loader];
        if (!new $class() instanceof Parser) throw new \Exception("The '{$options['loader']}' config loader is not intended to parse files", E_USER_ERROR);
        
        $parser = new $class($options);
        return $parser->parse($file);
    }
}
