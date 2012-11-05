<?php

namespace Jasny\Config;

use Jasny\Config;

require_once 'Parser.php';

/**
 * Load and parse .ini config files from a directory.
 *
 * @package Config
 */
class IniParser implements Parser
{
    /**
     * Create parser
     * 
     * @param array $options  Not used
     */
    public function __construct($options=array())
    { }
    
    /**
     * Parse ini file
     *
     * @param string $file  Filename
     * @return object
     */
    public function parse($file)
    {
        $data = parse_ini_file($file, true);
        return Config::objectify($data);
    }
}
