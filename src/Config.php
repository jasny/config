<?php

namespace Jasny;

use Jasny\Config\Loader;
use Jasny\Config\LoaderInterface;

/**
 * Configuration settings.
 */
class Config extends \stdClass
{
    /**
     * Class constructor
     * 
     * @param array|\stdClass $settings
     */
    public function __construct($settings = null)
    {
        if (isset($settings)) {
            if (!$settings instanceof \stdClass && !is_array($settings)) {
                $type = (is_object($settings) ? get_class($settings) . ' ' : '') . gettype($settings);
                throw new \InvalidArgumentException("Settings should be an array or stdClass object, not a $type."
                    . " Use the `load` method to load settings from a source.");
            }
            
            self::merge($this, static::objectify((object)$settings));
        }
    }
    
    
    /**
     * Get the loader
     * 
     * @param array $options
     * @return LoaderInterface
     */
    protected function getLoader(array $options = [])
    {
        return isset($options['loader']) && $options['loader'] instanceof LoaderInterface
            ? $options['loader'] : new Loader();
    }

    /**
     * Assert the loaded data
     * 
     * @param \stdClass|mixed $data
     * @throws \UnexpectedValueException
     */
    protected function assertData($data)
    {
        if (!$data instanceof \stdClass) {
            $type = (is_object($data) ? get_class($data) . ' ' : '') . gettype($data);
            throw new \UnexpectedValueException("Loader returned a $type instead of a Config object");
        }
    }
    
    /**
     * Load configuration
     * 
     * @param mixed $source   File name or other source
     * @param array $options  Loader options
     * @return $this
     */
    public function load($source, array $options = [])
    {
        $loader = $this->getLoader($options);
        $data = $loader->load($source, $options);
        
        if (isset($data)) {
            $this->assertData($data);
            static::merge($this, $data);
        }
        
        return $this;
    }

    
    /**
     * Assert each argument is a stdClass
     * 
     * @param \stdClass[] $args
     * @throws \InvalidArgumentException
     */
    protected static function assertMergeArguments(array $args)
    {
        foreach ($args as $i => $arg) {
            if (isset($arg) && !$arg instanceof \stdClass) {
                $type = (is_object($arg) ? get_class($arg) . ' ' : '') . gettype($arg);
                throw new \InvalidArgumentException("Argument " . ($i + 1) . " is not a stdClass object, but a $type");
            }
        }
    }
    
    /**
     * Recursive merge of 2 or more objects
     *
     * @param \stdClass $target  The object that will be modified
     * @param \stdClass $source  A source object 
     * @param \stdClass ...
     * @return \stdClass $target
     */
    public static function merge(&$target, ...$sources)
    {
        static::assertMergeArguments(func_get_args());
        
        foreach ($sources as $source) {
            if (!isset($source)) {
                continue;
            }
            
            if (!isset($target)) {
                $target = $source;
                continue;
            }
            
            $data = get_object_vars($source);
        
            foreach ($data as $key => $value) {
                if (isset($target->$key) && is_object($value) && is_object($target->$key)) {
                    static::merge($target->$key, $value);
                } else {
                    $target->$key = $value;
                }
            }
        }
        
        return $target;
    }
    
    /**
     * Turn an associative array into an stdClass object
     *
     * @param array|\stdClass $data
     * @return \stdClass
     */
    public static function objectify($data)
    {
        if (!is_array($data) && !$data instanceof \stdClass) {
            return $data;
        }
        
        // Check if it's an associative array
        if (is_array($data) && array_keys($data) !== array_keys(array_keys($data))) {
            $data = (object)$data;
        }
        
        foreach ($data as &$item) {
            $item = static::objectify($item);
        }
        
        return $data;
    }
}
