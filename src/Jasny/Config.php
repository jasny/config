<?php
/**
 * Configure your application.
 * 
 * @author Arnold Daniels
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
     * Multiton instances
     * @var Config
     */
    private static $instances;

    /**
     * Loader and parsers with classname.
     * @var array
     */
    static public $loaders = array(
        'file' => 'Jasny\Config\FileLoader',
        'dir' => 'Jasny\Config\DirLoader',
        'mysqli' => 'Jasny\Config\MySQLParser',
        'ini' => 'Jasny\Config\IniParser',
        'json' => 'Jasny\Config\JsonParser',
        'yaml' => 'Jasny\Config\YamlParser',
        'yml' => 'Jasny\Config\YamlParser'
    );
    
    /**
     * Get a registered instance
     * 
     * @return Config
     */
    static public function __callStatic($name, $arguments)
    {
        if (!isset(self::$instances[$name])) self::$instances[$name] = new static();
        return self::$instances[$name];
    }

    
    /**
     * Create a new config interface.
     *
     * @param string $source   Filename, source object or "loader:source"
     * @param array  $options  Other options
     */
    public function __construct($source=null, $options=array())
    {
        if ($source || $options) $this->load($source, $options);
    }

    
    /**
     * Get a loader for the specified source
     * 
     * @param type $source
     * @param type $options
     * @return Config\Loader
     */
    protected function getLoader($source, $options=array())
    {
        if (isset($options['loader'])) {
            $loader = $options['loader'];
        } elseif (is_object($source)) {
            foreach (self::$loaders as $cur=>$class) {
                if ($class instanceof Config\Loader && is_a($source, $cur)) {
                    $loader = $cur;
                    break;
                }
            }
            if (!isset($loader)) throw new \Exception("Don't know how to load config using " . get_class($source) . " object");
        }
        
        if (isset($loader)) {
            if (!isset(self::$loaders[$loader])) throw new \Exception("Config loader '$loader' does not exist");
            $class = self::$loaders[$loader];
        }
        
        if (!isset($class) || new $class() instanceof Config\Parser) {
            $class = is_dir($source) ? self::$loaders['dir'] : self::$loaders['file'];
        }
        
        return new $class();
    }
    
    /**
     * Create a new config interface.
     *
     * @param string $source   Filename, source object or "loader:source"
     * @param array  $options  Other options
     * @return Config
     */
    public function load($source, $options=array())
    {
        if (strpos($source, ':') !== false) list($options['loader'], $source) = explode(':', $source);
        $loader = $this->getLoader($source, $options);
        
        $data = $loader->load($source, $options);
        if (!$data) return $this;
        
        foreach ($data as $key=>&$value) {
            $this->$key = $value;
        }
        
        return $this;
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
