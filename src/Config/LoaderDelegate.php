<?php

namespace Jasny\Config;

use Jasny\Config\Loader;
use Jasny\Config\LoaderInterface;

/**
 * Get delegate loader
 */
trait LoaderDelegate
{
    /**
     * Get loader to deletegate loading files to
     * 
     * @param array $options
     * @return LoaderInterface
     */
    protected function getDelegateLoader(array $options)
    {
        $loader = isset($options['delegate_loader'])
            ? $options['delegate_loader']
            : new Loader();
        
        if (!$loader instanceof LoaderInterface) {
            $type = (is_object($loader) ? get_class($loader) . ' ' : '') . gettype($loader);
            throw new \UnexpectedValueException("Delegate loader is not a LoaderInterface but a $type");
        }
        
        return $loader;
    }
}