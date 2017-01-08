<?php

namespace Jasny\Config;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\LoaderDelegate;
use Jasny\ConfigException;

/**
 * Loader for directory with config files.
 */
class DirLoader implements LoaderInterface
{
    use LoaderDelegate;
    
    /**
     * Assert the directory exists
     * 
     * @param string $dir
     * @param array  $options
     * @return boolean
     * @throws ConfigException
     */
    protected function assertDir($dir, array $options)
    {
        if (!is_string($dir) && !(is_object($dir) && method_exists($dir, '__toString'))) {
            $type = (is_object($dir) ? get_class($dir) . ' ' : '') . gettype($dir);
            throw new \InvalidArgumentException("Expected a string as directory, got a $type");
        }
        
        if (is_dir((string)$dir)) {
            return true;
        }
        
        if (empty($options['optional'])) {
            throw new ConfigException("Config directory '$dir' doesn't exist");
        }

        return false;
    }
    
    
    /**
     * Load a file or subdirectory
     * 
     * @param string $file
     * @param array  $options
     * @return Config|null
     */
    protected function loadFile($file, array $options)
    {
        if (is_dir($file)) {
            return $this->load($file, $options);
        }
        
        $loader = $this->getDelegateLoader($options);
        
        return $loader->load($file, ['delegate_loader' => $loader] + $options);
    }
    
    /**
     * Add data to config
     * 
     * @param Config         $config
     * @param string         $file
     * @param \stdClass|null $data
     */
    protected function addDataToConfig(Config $config, $file, $data)
    {
        $key = pathinfo($file, PATHINFO_FILENAME);
        Config::merge($config->$key, $data);
    }
    
    /**
     * Load a config directory
     *
     * @param string $dir
     * @param array  $options
     * @return object
     */
    public function load($dir, array $options = [])
    {
        if (!$this->assertDir($dir, $options)) {
            return null;
        }
        
        $dir = rtrim((string)$dir, DIRECTORY_SEPARATOR);
        
        $config = new Config();
        
        foreach (scandir($dir) as $file) {
            if ($file[0] == '.') {
                continue;
            }
            
            $data = $this->loadFile($dir . DIRECTORY_SEPARATOR . $file, $options);
            
            if (isset($data)) {
                $this->addDataToConfig($config, $file, $data);
            }
        }
        
        return $config;
    }
}
