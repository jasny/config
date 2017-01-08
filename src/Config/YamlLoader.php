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
     * Get the parser
     *
     * @param array $options
     */
    public function getLoader(array $options)
    {
        $loader = !empty($options['use']) ? $options['use'] : $this->guessLoader();
        
        if (is_string($loader)) {
            $class = __NAMESPACE__ . '//Yaml' . ucfirst($use) . 'Loader';

            if (!class_exists($class)) {
                throw new ConfigException("Unable to parse yaml configuration file: $class does not exist");
            }
            
            $loader = new $class();
        }
        
        if (!$loader instanceof LoaderInterface) {
            $type = (is_object($loader) ? get_class($loader) . ' ' : '') . gettype($loader);
            throw new \UnexpectedValueException("$type doesn't implement LoaderInterface");
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
    public function loadFile($file, array $options)
    {
        $loader = $this->getLoader($options);
        
        return $loader->load($file, $options);
    }
}
