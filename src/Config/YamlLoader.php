<?php

namespace Jasny\Config;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\LoadFile;
use Jasny\ConfigException;

/**
 * Load and parse yaml config files.
 *
 * Options:
 *   use: Force the use of 'yaml', 'syck' or 'spyc'
 */
class YamlLoader implements LoaderInterface
{
    use LoadFile;
    
    /**
     * Guess which parser to use based on what's available.
     * @codeCoverageIgnore
     * 
     * @return string
     */
    public function guessLoader()
    {
        if (function_exists('yaml_parse')) {
            return 'yaml';
        }
        
        if (class_exists('Symfony\Component\Yaml\Yaml')) {
            return 'symfony';
        }
        
        if (class_exists('Spyc')) {
            return 'spyc';
        }
        
        throw new ConfigException("To load yaml configuration files you need the yaml extension, the Symfony YAML"
            . " component or the Spyc library.");
    }
    
    /**
     * Get loader class by name
     * 
     * @param string $use
     * @return string
     */
    protected function getLoaderClass($use)
    {
        $name = str_replace(' ', '', ucwords(preg_replace('/[\W_]+/', ' ', $use))); // StudlyCase
        
        return __NAMESPACE__ . '\Yaml' . $name . 'Loader';
    }
    
    /**
     * Get the parser
     *
     * @param array $options
     * @return LoaderInterface
     */
    public function getLoader(array $options)
    {
        $loader = isset($options['use']) ? $options['use'] : $this->guessLoader();
        
        if (is_string($loader)) {
            $class = $this->getLoaderClass($loader);

            if (!class_exists($class)) {
                throw new \BadMethodCallException("$class does not exist");
            }
            
            $loader = new $class();
        }
        
        if (!$loader instanceof LoaderInterface) {
            $type = (is_object($loader) ? get_class($loader) : gettype($loader));
            throw new \BadMethodCallException("$type doesn't implement LoaderInterface");
        }
        
        return $loader;
    }
    
    
    /**
     * Load a yaml file
     *
     * @param string $file
     * @param array  $options
     * @return Config
     */
    protected function loadFile($file, array $options)
    {
        $loader = $this->getLoader($options);
        
        return $loader->load($file, $options);
    }
}
