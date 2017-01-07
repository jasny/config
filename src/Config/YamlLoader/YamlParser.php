<?php

namespace Jasny\Config\YamlLoader;

use Jasny\Config\YamlLoader\Parser;

/**
 * Parse yaml file using the `yaml` PHP extension
 */
class YamlParser implements Parser
{
    /**
     * Parse a yaml file
     * 
     * @param string $file
     * @return array
     */
    public function parseFile($file)
    {
        return yaml_parse_file($file);
    }
}
