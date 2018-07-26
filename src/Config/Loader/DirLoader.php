<?php

declare(strict_types=1);

namespace Jasny\Config\Loader;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\Loader\DelegateLoader;
use Jasny\Config\Exception\LoadException;
use TypeError;

/**
 * Loader for directory with config files using the filename as key.
 */
class DirLoader implements LoaderInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var LoaderInterface
     */
    protected $fileLoader;

    /**
     * DirLoader constructor.
     *
     * @param array $options  Default options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }


    /**
     * Get the file loader
     *
     * @return LoaderInterface
     */
    public function getFileLoader(): LoaderInterface
    {
        if (!isset($this->fileLoader)) {
            $this->fileLoader = new DelegateLoader();
        }

        return $this->fileLoader;
    }

    /**
     * Set the file loader
     *
     * @param LoaderInterface $loader
     * @return $this
     */
    public function setFileLoader(LoaderInterface $loader): self
    {
        $this->fileLoader = $loader;

        return $this;
    }

    /**
     * Assert the directory exists
     * 
     * @param string $dir
     * @param array  $options
     * @return boolean
     * @throws LoadException
     */
    protected function assertDir(string $dir, array $options)
    {
        if (is_dir($dir)) {
            return true;
        }
        
        if (!(bool)($options['optional'] ?? false)) {
            throw new LoadException("Config directory '$dir' doesn't exist");
        }

        return false;
    }
    
    
    /**
     * Load a file or subdirectory
     * 
     * @param string $file
     * @param array  $options
     * @return Config|null
     */
    protected function loadFile(string $file, array $options): ?Config
    {
        if (is_dir($file)) {
            return (bool)($options['recursive'] ?? false) ? $this->load($file, $options) : null;
        }

        return $this->getFileLoader()->load($file, $options);
    }

    /**
     * Load a config directory
     *
     * @param string $dir
     * @param array  $options
     * @return Config
     */
    public function load($dir, array $options = [])
    {
        $config = new Config();

        if (!$this->assertDir($dir, $options)) {
            return $config;
        }
        
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        foreach (scandir($dir) as $file) {
            if ($file[0] == '.') {
                continue;
            }

            $key = pathinfo($file, PATHINFO_FILENAME);
            $data = $this->loadFile($dir . DIRECTORY_SEPARATOR . $file, $options);
            
            if (!isset($data)) {
                continue;
            }

            if (isset($config->$key) && $config->$key instanceof Config) {
                $config->$key->merge($data);
            } else {
                $config->$key = $data;
            }
        }
        
        return $config;
    }
}
