<?php

declare(strict_types=1);

namespace Jasny\Config\Loader;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\Exception\LoadException;
use Jasny\Config\Exception\NotFoundException;
use stdClass;

use function Jasny\is_associative_array;
use function Jasny\expect_type;
use function Jasny\str_contains;

/**
 * Base class to load a file.
 */
abstract class AbstractFileLoader implements LoaderInterface
{
    /**
     * @var array
     */
    protected $options;

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
     * Get the default options
     *
     * @return array
     */
    final public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Load the configuration from a file
     *
     * @param string $file
     * @param array  $options
     * @return array|stdClass
     */
    abstract protected function loadFile(string $file, array $options);


    /**
     * Turn a relative filename to an absolute path
     *
     * @param string $file
     * @param string $base
     * @return string
     */
    protected function absolutePath(string $file, string $base): string
    {
        return $file[0] !== DIRECTORY_SEPARATOR && !str_contains($file, ':')
            ? $base . DIRECTORY_SEPARATOR . $file
            : $file;
    }

    /**
     * Assert the file exists
     * 
     * @param string $file
     * @param array  $options
     * @return bool
     * @throws NotFoundException
     */
    protected function assertFile(string $file, array $options): bool
    {
        if (is_file($file) && is_readable($file)) {
            return true;
        }

        if (!(bool)($options['optional'] ?? false)) {
            throw new LoadException("Config file '$file' doesn't exist or is not readable");
        }

        return false;
    }


    /**
     * Assert that data has been property loaded
     *
     * @param stdClass|array|mixed $data
     * @param string               $file
     * @return void
     * @throws LoadException
     */
    protected function assertData($data, string $file): void
    {
        if ($data === false || $data === null) {
            throw new LoadException("Failed to load settings from '$file'");
        }

        if (!$data instanceof stdClass && !is_associative_array($data)) {
            throw new LoadException("Failed to load settings from '$file': data should be key/value pairs");
        }
    }

    /**
     * Load a config file or directory
     *
     * @param string $file`
     * @param array  $options
     * @return Config
     */
    public function load($file, array $options = []): Config
    {
        expect_type($file, 'string');

        $options += $this->options;
        $path = $this->absolutePath($file, $options['base_path'] ?? getcwd());

        if (!$this->assertFile($path, $options)) {
            return new Config();
        }

        $data = $this->loadFile($path, $options);
        $this->assertData($data, $file);

        return new Config($data);
    }
}
