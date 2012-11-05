<?php

namespace Jasny\Config;

/**
 * Config loader
 */
interface Loader
{
    /**
     * Load data
     * 
     * @param mixed $source   Filename or object
     * @param array $options  Additional options
     * @return object
     */
    public function load($source, $options=array());
}