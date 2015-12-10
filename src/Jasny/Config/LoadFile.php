<?php
/**
 * Jasny Config
 *
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/config/master/LICENSE MIT
 * @link    https://jasny.github.io/config
 */
/** */
namespace Jasny\Config;

use Jasny\Config;

/**
 * Load a file or directory.
 */
trait LoadFile
{
    /**
     * Load a config file or directory
     *
     * @param string $file
     * @return object
     */
    public function load($file)
    {
        if (!file_exists($file)) {
            if (empty($this->options['optional'])) trigger_error("Config file '$file' doesn't exist", E_USER_WARNING);
            return null;
        }

        return is_dir($file) ? $this->loadDir($file) : $this->loadFile($file);
    }
    
    
    /**
     * Load all files in a directory
     *
     * @param string $dir
     * @return object
     */
    protected function loadDir($dir)
    {
        $exts = array_keys(Config::$loaders, get_called_class());
        if (empty($exts)) return null;
        $glob = '*.' . (count($exts) == 1 ? reset($exts) : '{' . join(',', $exts) . '}');

        $config = (object)array();
        
        foreach (glob("$dir/$glob", GLOB_BRACE) as $file) {
            if (basename($file)[0] == '.') continue;
            
            $key = pathinfo($file, PATHINFO_FILENAME);
            $data = $this->load($file);
            if ($data) Config::merge($config->$key, $data);
        }
        
        return $config;
    }
    
    /**
     * Load the configuration from a file
     *
     * @param string $file
     * @return object
     */
    protected function loadFile($file)
    {
        $input = file_get_contents($file);
        return $this->parse($input);
    }
}

