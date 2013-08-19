<?php
/**
 * Jasny Config
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/config/master/LICENSE MIT
 * @link    https://jasny.github.io/config
 */
/** */
namespace Jasny\Config;

/**
 * Config loader
 */
abstract class Loader
{
    /** @var array */
    protected $options;

    /**
     * Class constructor
     * 
     * @param array $options  Additional options
     */
    public function __construct($options=[])
    {
        $this->options = (array)$options;
    }
    
    /**
     * Load configuration
     * 
     * @param string $key
     * @return object
     */
    abstract public function load($key);
}
