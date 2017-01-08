<?php

namespace Jasny\Config;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\LoadFile;
use Symfony\Component\Yaml\Yaml;

/**
 * Parse yaml file using the Symfony YAML component
 * 
 * options:
 *   flags - A set of PARSE_* constants to customize the YAML parser behavior
 */
class YamlSymfonyLoader implements LoaderInterface
{
    use LoadFile;
    
    /**
     * Turn flags array into binary set
     * 
     * @param array $names
     * @return int
     */
    protected function getFlagsByName(array $names)
    {
        $flags = 0;

        foreach ($names as $flag) {
            $flags |= constant(Yaml::class . '::' . $flag);
        }

        return $flags;
    }
    
    /**
     * Get parser flags
     * 
     * @param array $options
     * @return int
     */
    protected function getFlags(array $options)
    {
        if (!isset($options['flags'])) {
            return Yaml::PARSE_OBJECT_FOR_MAP;
        }
        
        return is_int($options['flags']) ? (int)$options['flags'] : $this->getFlagsByName((array)$options['flags']);
    }
    
    
    /**
     * Parse a yaml file
     * 
     * @param string $file
     * @param array  $options
     * @return Config
     */
    protected function loadFile($file, array $options)
    {
        $yaml = file_get_contents($file);
        $flags = $this->getFlags($options);
        
        $data = Yaml::parse($yaml, $flags);
        $this->assertData($data);
        
        return new Config($data);
    }
}
