<?php
/**
 * Jasny Config - Configure your application.
 *
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/config/master/LICENSE MIT
 * @link    https://jasny.github.io/config
 */
/** */
namespace Jasny\Config;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\ConfigException;

/**
 * Load and parse .ini config files from a directory.
 */
class IniLoader implements LoaderInterface
{
    use LoadFile;
    
    /**
     * Assert that data have been property loaded
     * 
     * @param array|false $data
     * @throws ConfigException
     */
    protected function assertData($data, $file)
    {
        if ($data === false) {
            throw new ConfigException("Failed to load settings from '$file' using " . get_class($this));
        }
    }
    
    /**
     * Parse ini file
     *
     * @param string $file    Filename
     * @param array  $options
     * @return Config
     */
    public function loadFile($file, array $options)
    {
        $data = parse_ini_file($file, true);
        $this->assertData($data, $file);
        
        return new Config($data);
    }
}
