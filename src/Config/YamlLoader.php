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
use Jasny\Config\LoaderInterface;
use Jasny\Config\LoadFile;
use Jasny\Config\YamlLoader\Parser;
use Jasny\ConfigException;

/**
 * Load and parse yaml config files.
 *
 * Options:
 *   use: Force the use of 'yaml', 'syck' or 'spyc'
 *
 * @package Config
 */
class YamlLoader implements LoaderInterface
{
    use LoadFile;
    
    /**
     * Guess which parser to use based on what's available
     */
    public function guessParser()
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
    public function getParser($options = [])
    {
        $parser = !empty($options['use']) ? $options['use'] : $this->guessParser();
        
        if (is_string($parser)) {
            $class = __CLASS__ . '//' . ucfirst($use) . 'Parser';

            if (!class_exists($class)) {
                throw new ConfigException("Unable to parse yaml configuration file: $class does not exist");
            }
            
            $parser = new $class();
        }
        
        if (!$parser instanceof Parser) {
            $type = (is_object($parser) ? get_class($parser) . ' ' : '') . gettype($parser);
            throw new \UnexpectedValueException("$type is not a yaml parser");
        }
        
        return $parser;
    }
    
    
    /**
     * Assert that data has been property loaded
     * 
     * @param \stdClass|array|mixed $data
     * @param string $file
     * @throws ConfigException
     */
    protected function assertData($data, $file)
    {
        if (!$data instanceof \stdClass && (!is_array($data) || array_keys($data) === array_keys(array_keys($data)))) {
            throw new ConfigException("Failed to parse yaml from '$file': data should be key/value pairs");
        }
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
        $parser = $this->getParser($options);
        
        $data = $parser->parseFile($file);
        $this->assertData($data, $file);
        
        return new Config($data);
    }
}
