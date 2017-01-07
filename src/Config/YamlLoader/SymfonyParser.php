<?php

namespace Jasny\Config\YamlLoader;

use Jasny\Config\YamlLoader\Parser;
use Symfony\Component\Yaml\Yaml as Yaml;

/**
 * Parse yaml file using the Symfony YAML component
 */
class SymfonyParser implements Parser
{
    /**
     * Parse a yaml file
     * 
     * @param string $file
     * @return array
     */
    public function parseFile($file)
    {
        $yaml = file_get_contents($file);
        
        return Yaml::parse($yaml);
    }
}
