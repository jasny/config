<?php

namespace Jasny\Config\YamlLoader;

/**
 * A parser for the YAML loader
 */
interface Parser
{
    /**
     * Parse a yaml file
     * 
     * @param string $file
     * @return array|\stdClass
     */
    public function parseFile($file);
}
