<?php

namespace Jasny\Config\Loader;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\Loader;
use Jasny\Config\Exception\NoLoaderException;
use UnexpectedValueException;

use function Jasny\expect_type;

/**
 * Delegate loading to other loader based on class name or file extension
 */
class DelegateLoader implements LoaderInterface
{
    /**
     * @var LoaderInterface[]
     */
    protected $loaders;

    /**
     * DelegateLoader constructor.
     *
     * @param LoaderInterface[] $loaders
     */
    public function __construct(array $loaders = null)
    {
        foreach ((array)$loaders as $key => $loader) {
            expect_type($loader, LoaderInterface::class, UnexpectedValueException::class,
                "Expected $key to be a LoaderInterface object, %s given");
        }

        $this->loaders = $loaders ?? static::getDefaultLoaders($this);
    }


    /**
     * Determine loader from source
     *
     * @param object|string $source
     * @return LoaderInterface
     * @throws NoLoaderException
     */
    protected function determineLoader($source): LoaderInterface
    {
        expect_type($source, ['object', 'string']);

        return is_object($source)
            ? $this->determineLoaderFromClass($source)
            : $this->determineLoaderFromPath($source);
    }

    /**
     * Determine the loader based on the classname of the object
     *
     * @param object $source
     * @return LoaderInterface
     * @throws NoLoaderException
     */
    protected function determineLoaderFromClass($source): LoaderInterface
    {
        foreach ($this->loaders as $key => $loader) {
            if (class_exists($key) && is_a($source, $key)) {
                return $loader;
            }
        }

        $desc = (is_object($source) ? get_class($source) . ' ' : '') . gettype($source);
        throw new NoLoaderException("Don't know how to load configuration from $desc");
    }

    /**
     * Determine loader based on file path
     * 
     * @param string $source
     * @return LoaderInterface
     * @throws NoLoaderException
     */
    protected function determineLoaderFromPath(string $source): LoaderInterface
    {
        $key = is_dir($source) ? 'dir' : (pathinfo($source, PATHINFO_EXTENSION) ?: null);

        if (!isset($key) || !isset($this->loaders[$key])) {
            $desc = is_scalar($source) ? "'$source'"
                : 'a ' . (is_object($source) ? get_class($source) . ' ' : '') . gettype($source);
            throw new NoLoaderException("Don't know how to load configuration from $desc");
        }

        return $this->loaders[$key];
    }
    
    
    /**
     * Load configuration settings
     * 
     * @param object|string $source
     * @param array         $options
     * @return Config
     */
    public function load($source, array $options = [])
    {
        return $this->determineLoader($source)->load($source, $options);
    }

    /**
     * Get the default loaders.
     *
     * @param LoaderInterface $fileLoader
     * @return LoaderInterface[]
     */
    public static function getDefaultLoaders(LoaderInterface $fileLoader = null): array
    {
        $loaders = [
            'dir' => new Loader\DirLoader(),
            'ini' => new Loader\IniLoader(),
            'json' => new Loader\JsonLoader(),
            'yaml' => function_exists('yaml_parse') ? new Loader\YamlLoader() : new Loader\YamlSymfonyLoader(),
            'Aws\DynamoDb\DynamoDbClient' => new Loader\DynamoDBLoader()
        ];

        $loaders['yml'] = $loaders['yaml'];

        if (isset($fileLoader)) {
            $loaders['dir']->setFileLoader($fileLoader);
        }

        return $loaders;
    }
}
