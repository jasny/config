<?php

namespace Jasny\Config;

use Jasny\Config;
use Jasny\Config\LoaderInterface;

/**
 * Composite loader
 */
class Loader implements LoaderInterface
{
    /**
     * @var array
     */
    protected $loaders = [
        'dir' => 'Jasny\Config\DirLoader',
        'Aws\DynamoDb\DynamoDbClient' => 'Jasny\Config\DynamoDBLoader',
        'mysqli' => 'Jasny\Config\MySQLLoader',
        'ini' => 'Jasny\Config\IniLoader',
        'json' => 'Jasny\Config\JsonLoader',
        'neon' => 'Jasny\Config\NeonLoader',
        'yaml' => 'Jasny\Config\YamlLoader',
        'yml' => 'Jasny\Config\YamlLoader'
    ];
    
    /**
     * @var array
     */
    protected $options = [];

    
    /**
     * Add, replace or remove a loader
     * 
     * @param string                      $key
     * @param string|LoaderInterface|null $loader
     */
    public function setLoader($key, $loader)
    {
        if ($class === null) {
            unset($this->loaders[$key]);
            return;
        }

        if (!is_a($loader, LoaderInterface::class, true)) {
            $class = is_string($loader) ? $loader : get_class($loader);
            throw new \InvalidArgumentException("$loader doesn't implement LoaderInterface");
        }
        
        $this->loaders[$key] = $loader;
    }
    
    /**
     * Get a loader by key
     * 
     * @param string $key
     * @return LoaderInterface
     * @throws Exception
     */
    public function getLoader($key)
    {
        if (!isset($this->loaders[$key])) {
            throw new \RangeException("Loader '$key' doesn't exist");
        }
        
        $loader = $this->loaders[$key];
        
        return is_string($loader) ? new $loader() : $loader;
    }
    

    /**
     * Determine loader from source
     * 
     * @param mixed $source
     * @return string
     */
    protected function determineLoader(&$source)
    {
        if (is_object($source)) {
            $loader = $this->determineLoaderFromClass($source);
        } elseif (is_string($source)) {
            $loader = $this->determineExplicitLoader($source) ?: $this->determineLoaderFromPath($source);
        }
        
        if (!isset($loader)) {
            $desc = is_scalar($source) ? "'$source'"
                : 'a ' . (is_object($source) ? get_class($source) . ' ' : '') . gettype($source);
            throw new \Exception("Don't know how to load configuration from $desc");
        }
    }
    
    /**
     * Extract explicit loader from source
     * 
     * @param string $source
     * @return string
     */
    protected function determineExplicitLoader(&$source)
    {
        if (preg_match('~^(\w+):/(?!\\\\)(?!//)(.+)$~', $source, $matches)) {
            list($loader, $source) = $matches;
        } else {
            $loader = null;
        }
        
        return $loader;
    }
    
    /**
     * Determine the loader based on the classname of the object
     * 
     * @param object $source
     * @return string|null
     */
    protected function determineLoaderFromClass($source)
    {
        $found = array_filter(array_keys($this->loaders), function ($loader) use ($source) {
            return is_a($source, $loader);
        });

        return reset($found) ?: null;
    }

    /**
     * Determin loader based on file path
     * 
     * @param string $source
     * @return string|null
     */
    protected function determineLoaderFromPath($source)
    {
        $key = is_dir($source) ? 'dir' : (pathinfo($source, PATHINFO_EXTENSION) ?: null);
        
        return isset($this->loaders[$key]) ? $key : null;
    }
    
    
    /**
     * Load configuration settings
     * 
     * @param mixed $source
     * @param array $options
     * @return Config
     */
    public function load($source, array $options = [])
    {
        $loaderKey = isset($options['loader']) ? $options['loader'] : $this->determineLoader($source);
        $loader = $this->getLoader($loaderKey);
        
        if (!isset($options['delegate_loader'])) {
            $options['delegate_loader'] = $this;
        }
        
        return $loader->load($source, $options);
    }
}
