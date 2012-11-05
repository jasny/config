<?php

namespace Jasny\Config;

use Jasny\Config;

require_once 'Loader.php';

/**
 * Loader for directories with config files.
 * 
 * @package Config
 */
class DirLoader implements Loader
{
    /**
     * Load a config directory
     * 
     * @param string $dir      Dirname
     * @param array  $options
     * @return object
     */
    public function load($dir, $options=array())
    {
        if (!is_dir($dir)) {
            if (empty($options['optional'])) trigger_error("Config directory '$dir' does not exist", E_USER_WARNING);
            return null;
        }
        
        $data = (object)array();
        
        foreach (scandir($dir) as $file) {
            if ($file[0] == '.') continue;
        
            if (is_dir("$dir/$file")) {
                $data->$file = $this->load("$dir/$file", $options);
            } else {
                if (isset($options['loader'])) {
                    $loader = $options['loader'];
                    if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) != $loader) continue;
                } else {
                    $loader = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                }
                
                if (!isset(Config::$loaders[$loader])) continue;
                
                $class = Config::$loaders[$loader];
                if (!new $class() instanceof Parser) continue;

                $key = pathinfo($file, PATHINFO_FILENAME);
                $parser = new $class($options);
                                
                $data->$key = $parser->parse("$dir/$file");
            }
        }
        
        return $data;
    }
}
