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
     * Load the configuration from a file
     *
     * @param string $file
     * @param array  $options
     * @return Config
     */
    protected function loadFile($file, array $options)
    {
        if (!class_exists('Nette\Neon')) {
            throw new ConfigException("To load config from neon file you need the Nette Neon library");
        }
        
        $neon = file_get_contents($file);
        
        $decoder = new Neon\Decoder();
        
        $data = $decoder->decode($neon);
        $this->assertData($data, $file);
        
        return new Config($data);
    }
}
