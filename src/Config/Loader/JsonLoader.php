<?php

declare(strict_types=1);

namespace Jasny\Config\Loader;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\Exception\LoadException;
use Jasny\Config\Loader\AbstractFileLoader;
use stdClass;

/**
 * Load and parse .json config files from a directory.
 */
class JsonLoader extends AbstractFileLoader
{
    /**
     * Assert that data have been property loaded
     * 
     * @param \stdClass|null|mixed $data
     * @param string $file
     * @throws LoadException
     */
    protected function assertData($data, string $file): void
    {
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new LoadException("Failed to load settings from '$file': " . json_last_error_msg());
        }

        if (!$data instanceof stdClass) {
            throw new LoadException("Failed to load settings from '$file': Data should be an object");
        }
    }
    
    /**
     * Load and parse json file
     *
     * @param string $file
     * @param array  $options
     * @return stdClass|mixed
     */
    protected function loadFile(string $file, array $options)
    {
        $json = file_get_contents($file);
        
        return json_decode($json, false, 512, JSON_BIGINT_AS_STRING);
    }
}
