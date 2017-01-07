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
use Jasny\Config\LoaderDelegate;
use Jasny\ConfigException;

/**
 * Load a file or directory.
 */
trait LoadFile
{
    use LoaderDelegate;
    
    /**
     * Load the configuration from a file
     *
     * @param string $file
     * @param array  $options
     * @return Config
     */
    abstract protected function loadFile($file, array $options);
    
    /**
     * Load all files in a directory
     *
     * @param string $dir
     * @return Config
     */
    protected function loadDir($dir, $options = [])
    {
        $loader = $this->getDelegateLoader($options);
        
        // The directory loader should always delegate loading files to this object
        $options['loader'] = 'dir';
        $options['delegate_loader'] = $this;
        
        return $loader->load($dir, $options);
    }

    
    /**
     * Assert the file exists
     * 
     * @param string $file
     * @param array  $options
     * @return boolean
     * @throws ConfigException
     */
    protected function assertFile($file, array $options)
    {
        if (!is_readable($file)) {
            if (empty($options['optional'])) {
                throw new ConfigException("Config file '$file' doesn't exist or is not readable");
            }
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Load a config file or directory
     *
     * @param string $file
     * @param array  $options
     * @return Config
     */
    public function load($file, array $options = [])
    {
        if (!$this->assertFile($file, $options)) {
            return null;
        }

        return is_dir($file) ? $this->loadDir($file, $options) : $this->loadFile($file, $options);
    }
}
