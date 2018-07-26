<?php

declare(strict_types=1);

namespace Jasny;

use Jasny\Config\Exception\NotFoundException;
use Jasny\Config\LoaderInterface;
use Jasny\Config\Loader\DelegateLoader;
use Psr\Container\ContainerInterface;
use Jasny\DotKey;
use stdClass;

use function Jasny\expect_type;
use function Jasny\objectify;
use function Jasny\str_starts_with;
use function Jasny\str_contains;

/**
 * Configuration settings.
 */
class Config extends stdClass implements ContainerInterface
{
    /**
     * @var LoaderInterface
     */
    protected $i__loader;

    /**
     * Class constructor
     * 
     * @param array|stdClass  $settings
     * @param LoaderInterface $loader
     */
    public function __construct($settings = [], LoaderInterface $loader = null)
    {
        expect_type($settings, ['stdClass', 'array']);
        static::mergeObjects($this, objectify((object)$settings));

        if (isset($loader)) {
            $this->i__loader = $loader;
        }
    }

    /**
     * Get the loader
     * 
     * @return LoaderInterface
     */
    public function getLoader(): LoaderInterface
    {
        if (!isset($this->i__loader)) {
            $this->i__loader = new DelegateLoader();
        }

        return $this->i__loader;
    }

    /**
     * Load configuration
     * 
     * @param mixed $source   File name or other source
     * @param array $options  Loader options
     * @return $this
     */
    public function load($source, array $options = []): self
    {
        $loader = $this->getLoader();
        $data = $loader->load($source, $options);

        expect_type($data, stdClass::class);

        static::mergeObjects($this, $data);

        return $this;
    }

    /**
     * Merge with other configuration
     *
     * @param stdClass[] $sources  Source objects
     * @return $this
     */
    public function merge(stdClass ...$sources): self
    {
        static::mergeObjects($this, ...$sources);

        return $this;
    }

    /**
     * Recursive merge of 2 or more objects
     *
     * @param stdClass   $target   The object that will be modified
     * @param stdClass[] $sources  Source objects
     * @return void
     */
    protected static function mergeObjects(stdClass &$target, stdClass ...$sources): void
    {
        foreach ($sources as $source) {
            foreach ($source as $key => $value) {
                if (str_starts_with($key, 'i__')) {
                    continue;
                }

                if (isset($target->$key) && is_object($value) && is_object($target->$key)) {
                    static::mergeObjects($target->$key, $value);
                } else {
                    $target->$key = $value;
                }
            }
        }
    }


    /**
     * Finds an entry of the container by its identifier and returns it.
     * Supports dot key notation.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        expect_type($key, 'string');

        $value = DotKey::on($this)->get($key);

        if (!isset($value)) {
            throw new NotFoundException("Config setting '$key' not found");
        }

        return $value;
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Supports dot key notation.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        expect_type($key, 'string');

        return DotKey::on($this)->exists($key);
    }


    /**
     * Save configuration as PHP script.
     * This will speed up loading the configuration.
     *
     * @param string $filename
     * @return void
     */
    public function saveAsScript(string $filename): void
    {
        $config = clone $this;
        unset($config->i__loader);

        $export = str_replace('stdClass::__set_state', '(object)', var_export($config, true));
        $script = "<?php\nreturn $export;";

        $success = file_put_contents($filename, $script, str_contains($filename, ':') ? 0 : LOCK_EX);

        if ($success && function_exists('opcache_compile_file')) {
            // @codeCoverageIgnoreStart
            opcache_compile_file($filename);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Load configuration from PHP script.
     *
     * @param string $filename
     * @return Config|null
     */
    public static function loadFromScript(string $filename): ?Config
    {
        if (
            (function_exists('opcache_is_script_cached') && opcache_is_script_cached($filename)) ||
            file_exists($filename)
        ) {
            return include $filename ?: null;
        }

        return null;
    }

    /**
     * Revive from var_export
     *
     * @param array $data
     * @return static
     */
    public static function __set_state(array $data): self
    {
        $config = new static();

        return $config->merge((object)$data);
    }
}
