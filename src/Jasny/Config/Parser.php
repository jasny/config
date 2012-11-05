<?php

namespace Jasny\Config;

/**
 * Config file parser
 */
interface Parser
{
    /**
     * Create parser
     * 
     * @param array $options
     */
    public function __construct($options=array());
    
    /**
     * Pase a file
     * 
     * @param mixed $file  Filename
     * @return object
     */
    public function parse($input);
}