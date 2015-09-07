<?php
/**
 * Jasny Config - Configure your application.
 *
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/config/master/LICENSE MIT
 * @link    https://jasny.github.io/config
 */
/** */
namespace Jasny\Config;

use Jasny\Config;

/**
 * Loader for directories with config files.
 */
class DirLoader extends Loader
{
    /**
     * Load a config directory
     *
     * @param string $dir
     * @return object
     */
    public function load($dir)
    {
        if (!is_dir($dir)) {
            if (empty($this->options['optional'])) {
                trigger_error("Config directory '$dir' doesn't exist", E_USER_WARNING);
            }
            return null;
        }
        
        $config = (object)array();
        
        foreach (scandir($dir) as $file) {
            if ($file[0] == '.') continue;
        
            if (is_dir("$dir/$file")) {
                $data = $this->load("$dir/$file");
            } else {
                $loader = Config::getLoader("$dir/$file", $this->options);
                if (!$loader) continue;
                
                $data = $loader->load("$dir/$file");
            }
            
            if ($data) {
                $key = pathinfo($file, PATHINFO_FILENAME);
                Config::merge($config->$key, $data);
            }
        }
        
        return $config;
    }
}
