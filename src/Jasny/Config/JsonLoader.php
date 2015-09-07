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

/**
 * Load and parse .json config files from a directory.
 *
 * @package Config
 */
class JsonLoader extends Loader
{
    use LoadFile;
    
    /**
     * Parse json string
     *
     * @param string $input  JSON string
     * @return object
     */
    public function parse($input)
    {
        $data = json_decode($input);
        
        if (json_last_error()) trigger_error("Failed to parse json file: " .
                $this->getJsonError(json_last_error()), E_USER_WARNING);
        return $data;
    }
    
    /**
     * Get an error message for a json error
     *
     * @param int $errno
     * @return string
     */
    protected function getJsonError($errno)
    {
        if (!is_int($errno)) $errno = 9999;
    
        switch ($errno) {
            case JSON_ERROR_NONE:
                return 'No error';
            break;
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
            default:
                return 'Unknown error';
            break;
        }
    }
}
