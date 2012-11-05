<?php

namespace Jasny\Config;

require_once 'Parser.php';

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
     * Parse yaml file or string
     *
     * @param string $file  Filename
     * @return object
     */
    public function parse($file)
    {
        if (function_exists('yaml_parse_file')) {
            $data = yaml_parse_file($file);
        } elseif (function_exists('syck_load')) {
            $data = syck_load(file_get_contents($file));
        } else {
            trigger_error("Unable to parse a yaml file. Neither the yaml or syck extensions are loaded.", E_USER_WARNING);
            return null;
        }
        
        return Config::objectify($data);
    }
}
