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
 * Load and parse .yaml config files from a directory.
 *
 * @package Config
 */
class YamlParser implements Parser
{
    /**
     * Create parser
     * 
     * @param array $options  Not used
     */
    public function __construct($options=array())
    { }
    
    
    /**
     * Parse yaml string
     *
     * @param string $input
     * @return object
     */
    public function parse($input)
    {
        if (function_exists('yaml_parse')) {
            $data = yaml_parse($input);
        } elseif (function_exists('syck_load')) {
            $data = syck_load($input);
        } elseif (class_exists('Spyc')) {
            $data = Spyc::load($input);
        } else {
            throw new \Exception("Unable to parse a yaml file. Need the yaml or syck extension or the spyc library.");
        }
        
        return Config::objectify($data);
    }
}
