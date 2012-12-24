<?php
namespace Jasny\Config;

require_once 'Parser.php';

/**
 * Load and parse .json config files from a directory.
 *
 * @package Config
 */
class JsonParser implements Parser
{
    /**
     * Create parser
     * 
     * @param array $options  Not used
     */
    public function __construct($options=array())
    { }
    
    /**
     * Parse json file or string
     *
     * @param string $file  Filename
     * @return object
     */
    public function parse($file)
    {
        $json = file_get_contents($file);
        $data = json_decode($json);
        
        if (json_last_error()) trigger_error("Failed to parse json file: " . $this->getJsonError(json_last_error()), E_USER_WARNING);
        return $data;
    }
    

    private function getJsonError($errno)
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return 'No errors';
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
