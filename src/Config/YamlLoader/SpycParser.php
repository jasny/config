<?php

namespace Jasny\Config\YamlLoader;

use Jasny\Config\YamlLoader\Parser;
use Spyc;

/**
 * Parse yaml file using the Spyc library
 */
class SpycParser implements Parser
{
    /**
     * Parse a yaml file
     * 
     * @param string $file
     * @return array
     */
    public function parseFile($file)
    {
        return Spyc::YAMLLoad($file);
    }
}
