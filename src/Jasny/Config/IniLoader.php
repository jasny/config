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

/**
 * Load and parse .ini config files from a directory.
 */
class IniLoader extends Loader
{
    use LoadFile;
    
    /**
     * Parse ini file
     *
     * @param string $file  Filename
     * @return object
     */
    public function loadFile($file)
    {
        $data = parse_ini_file($file, true);
        return Config::objectify($data);
    }
    
    /**
     * Parse ini string
     *
     * @param string $input
     * @return object
     */
    public function parse($input)
    {
        $data = parse_ini_string($input, true);
        return Config::objectify($data);
    }
}
