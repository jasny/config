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
 * Load and parse yaml config files.
 * 
 * Options:
 *   use: Force the use of 'yaml', 'syck' or 'spyc'  
 *
 * @package Config
 */
class YamlLoader extends Loader
{
    use LoadFile;
    
    /**
     * Create Loader
     * 
     * @param array $options
     */
    public function __construct($options=[])
    {
        if (empty($options['use'])) {
            $options['use'] = null;
            
            if (function_exists('yaml_parse')) $options['use'] = 'yaml';
             elseif (function_exists('syck_load')) $options['use'] = 'syck';
             elseif (class_exists('Spyc')) $options['use'] = 'spyc';
             else trigger_error("To use yaml files you need the yaml or syck extension or the Spyc library."
                     , E_USER_WARNING);
        }
        
        parent::__construct($options);
    }
    
    
    /**
     * Load a yaml file
     * 
     * @param string $file
     */
    public function loadFile($file)
    {
        switch ($this->options['use']) {
            case 'yaml': $data = yaml_parse_file($file); break;
            case 'syck': $data = syck_load(file_get_contents($file)); break;
            case 'spyc': $data = \Spyc::YAMLLoad($file); break;
            default: return null;
        }
        
        return Config::objectify($data);
    }
    
    /**
     * Parse yaml string
     *
     * @param string $input
     * @return object
     */
    public function parse($input)
    {
        switch ($this->options['use']) {
            case 'yaml': $data = yaml_parse($input); break;
            case 'syck': $data = syck_load($input); break;
            case 'spyc': $data = \Spyc::YAMLLoadString($input); break;
            default: return null;
        }
        
        return Config::objectify($data);
    }
}
