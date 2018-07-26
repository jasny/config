<?php

declare(strict_types=1);

namespace Jasny\Config\Loader;

use Jasny\Config\Loader\AbstractFileLoader;
use Jasny\Config\Exception\LoadException;

use function Jasny\str_contains;
use function Jasny\is_associative_array;

/**
 * Parse yaml file using the `yaml` PHP extension
 */
class YamlLoader extends AbstractFileLoader
{
    /**
     * Assert that data have been property loaded
     *
     * @param array|false|mixed $data
     * @param string            $file
     * @throws LoadException
     */
    protected function assertData($data, string $file): void
    {
        if ($data === false && error_get_last() !== null) {
            $err = preg_replace('/^yaml_\w+\(\): /', '', error_get_last()["message"]);
            throw new LoadException("Failed to load settings from '$file': $err");
        }

        if (!is_array($data) || !is_associative_array($data)) {
            throw new LoadException("Failed to load settings from '$file': data should be key/value pairs");
        }
    }

    /**
     * Parse a yaml file.
     *
     * {@internal yaml_parse_file don't handle PHP streams well and yaml_parse_url is very strict with ---}
     * 
     * @param string $file
     * @param array  $options
     * @return array|mixed
     */
    protected function loadFile(string $file, array $options)
    {
        $yaml = file_get_contents($file);

        // Workaround for the bad practise of PHP extensions to trigger a warning and return false.
        error_clear_last();

        $unused = null;

        $data = @yaml_parse(
            $yaml,
            $options['pos'] ?? 0,
            $unused,
            $options['callbacks'] ?? []
        );

        return $data;
    }
}
