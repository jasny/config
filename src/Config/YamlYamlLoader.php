<?php

namespace Jasny\Config;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\LoadFile;

/**
 * Parse yaml file using the `yaml` PHP extension
 */
class YamlYamlLoader implements LoaderInterface
{
    use LoadFile;
    
    /**
     * Parse a yaml file
     * 
     * @param string $file
     * @param array  $options
     * @return Config
     */
    protected function loadFile($file, array $options)
    {
        $data = yaml_parse_file($file);
        $this->assertData($data, $file);
        
        return new Config($data);
    }
}
