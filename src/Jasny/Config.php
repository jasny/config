<?php
/**
 * Jasny Config - Configure your application.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/config/master/LICENSE MIT
 * @link    https://jasny.github.io/config
 */
/** */
namespace Jasny;

/**
 * Load a configuration settings.
 * 
 * Note: You need to use an autoloader of include loaders manualy.
 * 
 * @package Config
 */
class Config extends \stdClass
{
    /**
     * Loader and parsers with classname.
     * @var array
     */
    static public $loaders = [
        'dir' => 'Jasny\Config\DirLoader',
        'mysqli' => 'Jasny\Config\MySQLLoader',
        'ini' => 'Jasny\Config\IniLoader',
        'json' => 'Jasny\Config\JsonLoader',
        'neon' => 'Jasny\Config\NeonLoader',
        'yaml' => 'Jasny\Config\YamlLoader',
        'yml' => 'Jasny\Config\YamlLoader'
    ];

    
    /**
     * Create a new config interface.
     *
     * @param mixed $source   Configuration (array|object), filename (string), source object or "loader:source"
     * @param array $options  Other options
     */
    public function __construct($source=null, $options=[])
    {
        if (is_array($source) || $source instanceof stdClass) {
            static::merge($this, $source);
        } elseif (isset($source)) {
            $this->load($source, $options);
        }
    }
    
    
    /**
     * Get a loader for the specified source.
     * 
     * @param mixed $source
     * @param array $options
     * @return Config\Loader
     */
    public static function getLoader($source, $options=[])
    {
        if (isset($options['loader'])) {
            $loader = $options['loader'];
        } elseif (is_object($source)) {
            foreach (self::$loaders as $cur=>$class) {
                if (is_a($source, $cur)) {
                    $loader = $cur;
                    break;
                }
            }
            
            if (!isset($loader)) {
                trigger_error("Don't know how to load config using " . get_class($source) . " object", E_USER_WARNING);
                return null;
            }
        } elseif (is_dir($source)) {
            $loader = 'dir';
        } else {
            $loader = pathinfo($source, PATHINFO_EXTENSION);
        }
        
        if (!isset(self::$loaders[$loader])) return null;
        $class = self::$loaders[$loader];
        
        return new $class($options);
    }
    
    /**
     * Create a new config interface.
     *
     * @param string $source   Filename, source object or "loader:source"
     * @param array  $options  Other options
     * @return Config
     */
    public function load($source, $options=[])
    {
        /**
         * Support windows platforms
         */
        if('winnt' == strtolower(PHP_OS)){
            if(count(explode(':', $source))==3){
                $exploded = explode(':', $source);
                $options['loader'] = $exploded[0];
                unset($exploded[0]);
                $source = implode(':',$exploded);
            }
        }else{
            if (strpos($source, ':') !== false) list($options['loader'], $source) = explode(':', $source);
        }

        $loader = static::getLoader($source, $options);
        
        if ($loader) $data = $loader->load($source);
         else trigger_error("Config loader '$loader' does not exist", E_USER_WARNING);
        
        if (!isset($data)) return $this;
        
        static::merge($this, $data);
        return $this;
    }
    

    /**
     * Recursive merge of 2 objects
     * 
     * @param object $target
     * @param object $data
     */
    public static function merge(&$target, $data)
    {
        if (!isset($target)) {
            $target = $data;
            return;
        }
        
        foreach ($data as $key=>&$value) {
            if (isset($target->$key) && is_object($value) && is_object($target->$key)) {
                static::merge($target->$key, $value);
            } else {
                $target->$key = $value;
            }
        }
    }
    
    /**
     * Turn associated array to object
     * 
     * @param array $data
     * @return object
     */
    public static function objectify($data)
    {
        if (!is_array($data)) return $data;
        
        if (!is_int(key($data))) $data = (object)$data;
        
        foreach ($data as &$item) {
            $item = self::objectify($item);
        }
        return $data;
    }
}
