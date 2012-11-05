<?php
namespace Jasny\Config;

require_once 'Parser.php';

/**
 * Load and parse .json config files from a directory.
 *
 * @package Config
 */
class JsonParser implements Parser
{
    /**
     * Create parser
     * 
     * @param array $options  Not used
     */
    public function __construct($options=array())
    { }
    
    /**
     * Parse json file or string
     *
     * @param string $file  Filename
     * @return object
     */
    public function parse($file)
    {
        $json = file_get_contents($file);
        return json_decode($json);
    }
}
