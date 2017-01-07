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
 * Load and parse .json config files from a directory.
 *
 * @package Config
 */
class JsonLoader implements LoaderInterface
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
        if ($data === null && json_last_error()) {
            $error = $this->getJsonError(json_last_error());
            throw new ConfigException("Failed to parse json from '$file': $error");
        }
        
        if (!$data instanceof \stdClass) {
            throw new ConfigException("Failed to parse json from '$file': data should be an object");
        }
    }
    
    /**
     * Parse json string
     *
     * @param string $file
     * @param array  $options
     * @return object
     */
    public function loadFile($file, array $options)
    {
        $json = file_get_contents($file);
        
        $data = json_decode($json);
        $this->assertData($data, $file);
        
        return new Config($data);
    }
    
    /**
     * Get an error message for a json error
     *
     * @param int $errno
     * @return string
     */
    protected function getJsonError($errno)
    {
        if (!is_int($errno)) {
            $errno = 9999;
        }
        
        switch ($errno) {
            case JSON_ERROR_NONE:
                return 'No error';
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default:
                return 'Unknown error';
        }
    }
}

