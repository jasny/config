<?php

declare(strict_types=1);

namespace Jasny\Config\Loader;

use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\Loader\AbstractFileLoader;
use Jasny\Config\Exception\LoadException;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use BadMethodCallException;
use stdClass;

/**
 * Parse yaml file using the Symfony YAML component
 */
class YamlSymfonyLoader extends AbstractFileLoader
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * YamlSymfonyLoader constructor.
     *
     * @param array  $options
     * @param Parser $parser
     */
    public function __construct(array $options = [], Parser $parser = null)
    {
        parent::__construct($options);

        $this->parser = $parser ?? new Parser();
    }

    /**
     * Parse a yaml file
     * 
     * @param string $file
     * @param array  $options
     * @return \stdClass|mixed
     */
    protected function loadFile(string $file, array $options)
    {
        if (isset($options['num']) || isset($options['callbacks'])) {
            throw new BadMethodCallException("Options 'num' and 'callbacks' aren't supported with the Symfony"
                ." YAML parser. Please install the yaml PHP extension.");
        }

        try {
            return $this->parser->parseFile($file, Yaml::PARSE_OBJECT_FOR_MAP);
        } catch (ParseException $parseException) {
            $err = $parseException->getMessage();
            throw new LoadException("Failed to load settings from '$file': {$err}", 0, $parseException);
        }
    }
}
