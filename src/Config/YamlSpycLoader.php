<?php

namespace Jasny\Config;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\LoadFile;
use Spyc;

/**
 * Parse yaml file using the Spyc library
 */
class YamlSpycLoader implements LoaderInterface
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
        $data = Spyc::YAMLLoad($file);
        $this->assertData($data);
        
        return new Config($data);
    }
}
