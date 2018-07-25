<?php

declare (strict_types = 1);

namespace Jasny\Config\Loader;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\Exception\LoadException;
use Jasny\DotKey;
use BadMethodCallException;

/**
 * Load settings from environment variables
 */
class EnvLoader implements LoaderInterface
{
    /**
     * @var array
     */
    protected $mapping;

    /**
     * Calls getenv for local and system env vars
     *
     * @param string $varname
     * @return string|null
     */
    protected function getenv($varname): ?string
    {
        $value = getenv($varname, true);

        if ($value === false) {
            $value = getenv($varname);
        }

        return $value !== false ? $value : null;
    }

    /**
     * Load settings from environment
     *
     * @param mixed $source  Ignored
     * @param array $options
     * @return Config
     */
    public function load($source, array $options = []): Config
    {
        if (!isset($options['map'])) {
            throw new BadMethodCallException("No 'map' option specified");
        }

        $config = new Config();
        $dotkey = DotKey::on($config);

        $vars = getenv();

        foreach ($options['map'] as $env => $key) {
            $value = $this->getenv($env);

            if (isset($value)) {
                $dotkey->put($key, $value);
            }
        }

        return $config;
    }
}
