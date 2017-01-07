<?php

namespace Jasny\Config;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Nette\Neon;

/**
 * Config loader for Nette Object Notation.
 */
class NeonLoader implements LoaderInterface
{
    use LoadFile;
    
    /**
     * Assert that data have been property loaded
     * 
     * @param \stdClass|null|mixed $data
     * @param string $file
     * @throws ConfigException
     */
    protected function assertData($data, $file)
    {
        if (empty($data)) {
            throw new ConfigException("Failed to parse neon from '$file'");
        }
        
        if (!$data instanceof \stdClass && !is_array($data)) {
            throw new ConfigException("Failed to parse neon from '$file': data should be key/value pairs");
        }
    }
    
    /**
     * Load the configuration from a file
     *
     * @param string $file
     * @param array  $options
     * @return Config
     */
    protected function loadFile($file, array $options)
    {
        $neon = file_get_contents($file);
        
        $decoder = new Neon\Decoder();
        
        $data = $decoder->decode($neon);
        $this->assertData($data, $file);
        
        return new Config($data);
    }
}
