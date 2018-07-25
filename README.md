Jasny's Config class
====================

[![Build Status](https://secure.travis-ci.org/jasny/config.png?branch=master)](http://travis-ci.org/jasny/config)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/config/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/config/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/config/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/config/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/9d9ee2fe-1622-4883-b46e-aa21b14a3931/mini.png)](https://insight.sensiolabs.com/projects/9d9ee2fe-1622-4883-b46e-aa21b14a3931)

Configure your application. Implements [PSR-11](https://www.php-fig.org/psr/psr-11/) to be used as container.

## Installation

Jasny Config is registred at packagist as [jasny/config](https://packagist.org/packages/jasny/config) and can be
easily installed using [composer](http://getcomposer.org/).

    composer require jasny/config

## Usage

```php
use Jasny\Config;

$config = (new Config())
  ->load('settings.yml')
  ->load('settings.dev.yml', ['optional' => true]);
```

## Loaders

* Dir
* Ini
* Json
* Yaml
  * Symfony
  * Yaml extension
* DynamoDB

The `DelegateLoader` can pick a loader based on class name or file extension.

For every loader; a `Jasny\Config\LoadException` is thrown if the loader can't load the settings. 

### Dir

`DirLoader` loader will traverse through every file in a directory. It uses the filename (without extension) as key name.

| option    | type   | default  |                                                |
| --------- | ------ | -------- | ---------------------------------------------- |
| recursive | bool   | false    | Traverse recursively through subdirectories    | 
| optional  | bool   | false    | Return empty config is directory doesn't exist | 
| base_path | string | getcwd() | Basepath for relative paths                    |

### Ini

`IniLoader` parses an INI file using [`parse_ini_file`](http://php.net/parse_ini_file).

| option           | type   | default            |                                                  |
| ---------------- | ------ | ------------------ | ------------------------------------------------ |
| process_sections | bool   | false              | Group settings by section name                   |
| mode             | enum   | INI_SCANNER_NORMAL | Mode of parsing options (off = false, etc)       |
| optional         | bool   | false              | Return empty config is file doesn't exist        | 
| base_path        | string | getcwd()           | Basepath for relative paths                      |

### Json

`JsonLoader` parses a JSON file using [`json_decode`](http://php.net/json_decode).

| option           | type   | default            |                                                  |
| ---------------- | ------ | ------------------ | ------------------------------------------------ |
| optional         | bool   | false              | Return empty config is file doesn't exist        | 
| base_path        | string | getcwd()           | Basepath for relative paths                      |

### Yaml

`YamlLoader` parses a YAML file using [`yaml_parse`](http://php.net/yaml_parse).

| option           | type   | default            |                                                  |
| ---------------- | ------ | ------------------ | ------------------------------------------------ |
| num              | int    | 0                  | Document to extract from stream (0 is first doc) |
| callbacks        | array  | []                 | Content handlers for YAML nodes                  |             
| optional         | bool   | false              | Return empty config is file doesn't exist        | 
| base_path        | string | getcwd()           | Basepath for relative paths                      |

The `YamlLoader` is used by default if the `yaml` extension is loaded.

#### Symfony\Yaml 

`YamlSymfonyLoader` is an alternative loader for YAML, using the [Symfony Yaml component](https://symfony.com/doc/current/components/yaml.html).  

| option           | type   | default            |                                                  |
| ---------------- | ------ | ------------------ | ------------------------------------------------ |
| optional         | bool   | false              | Return empty config is file doesn't exist        | 
| base_path        | string | getcwd()           | Basepath for relative paths                      |

When constructing the loader a `Symfony\Component\Yaml\Parser` object may be passed.

```php
use Symfony\Component\Yaml;

$parser = new class() extends Yaml\Parser() {
    public function parse($value, $options = 0) {
        return parent::parseFile($value, $options | Yaml\Yaml::PARSE_CONSTANT); 
    }
}

$yamlLoader = new YamlSymfonyLoader($options, $parser);
```

### DynamoDB

`DynamoDBLoader` allows loading settings from an AWS DynamoDB database. It expects all settings to be in a single item.
It will *not* do a table scan. By default the whole item is taken as settings. Alternatively, use the `settings_field`
option to specify a [Map][] field that holds the settings.

| option           | type   | default            |                                                  |
| ---------------- | ------ | ------------------ | ------------------------------------------------ |
| table            | string | (required)         | DynamoDB table name                              |
| key_field        | string | 'key'              | Indexed field (typically primary index)          |             
| key_value        | string | (required)         | Value to select item on                          |             
| settings_field   | string | null               |  within the item that holds the settings         | 

**Example**

```php
$dynamodb = Aws\DynamoDb\DynamoDbClient::factory([
    'region' => 'eu-west-1',
    'version' => '2012-08-10'
]);
 *
$config = new Jasny\Config();

$config->load($dynamodb, [
   'table' => 'config',
   'key_field' => 'key',
   'key_value' => 'myapp'
]);
```

[Map]: https://docs.aws.amazon.com/amazondynamodb/latest/developerguide/HowItWorks.NamingRulesDataTypes.html#HowItWorks.DataTypes.Document.Map

### DelegateLoader

The `DelegateLoader` is a map with loaders, than can automatically pick a loader based on the file extension or class
name.

By default it holds all the loaders of this library. You may pass an array of loaders as dependency injection.

```php
use Jasny\Config\Loader;

$loader = new Loader\DelegateLoader([
    'yaml' => new Loader\YamlLoader(['base_path' => __DIR__ . '/config']),
    'json' => new Loader\JsonLoader(['base_path' => __DIR__]) 
]);

$config = new Config([], $loader);
$config->load('composer.json');
$config->load('settings.yml');
```

## Config

The `Config` object is a dynamic object; settings are added as public properties. It extends the `stdClass` object.

Upon construction you can pass settings as associative array or `stdClass` object.

```php
$config = new Jasny\Config([
    'foo' => 'bar',
    'db' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'god'
    ]
]);
```

Optionally you can pass a loader. If your config is only in JSON files, consider passing the `JsonLoader`. This saves
the creation of the DelegateLoader and loaders for all other file types.

### load()

Load new setting from file or other data source and add it to the config.

`load(string|object $source, array $options = [])`

_This is a fluent interface, so the method returns `$this`._

**Example**

```php
$config = (new Config())
    ->load('composer.json')
    ->load('config/settings.yml')
    ->load($dynamoDB, ['table' => 'config', 'key_value' => 'myapp']);
```

### merge()

Merge one or more `Config` (or `stdClass`) objects into the config.

`merge(stdClass $settings, ...)`

_This is a fluent interface, so the method returns `$this`._

### get()

The `Config` object implements the [PSR-11 `ContainerInterface`](https://www.php-fig.org/psr/psr-11/#31-psrcontainercontainerinterface).
The `get` method finds a setting in the config by key. The dot notation may be used to get child properties.

`mixed get(string $key)`

If the setting doesn't exist, a `Jasny\Config\Exception\NotFoundException` is thrown.

**Example**

```php
$config->get('foo'); // bar
$config->get('db.host'); // localhost

// throws a NotFoundException
$config->get('something_random');
```

### has()

The `has` method is part of the PSR-11 `ContainerInterface`. It checks if a setting exists. As with `get()`, the key
supports the dot notation.

`bool has(string $key)`

### saveAsScript() / loadFromScript()

The `saveAsScript()` method create parsable string representation of a variable using
[`var_export`](https://php.net/var_export) and store it as a PHP script.

As described in the [500x faster caching][] article, storing the parsed configuration as a PHP script can make loading
it much faster.

The `loadFromScript()` method can be used to load the settings. It's recommended to use this function rather than just
`file_exists` and `include`, as checking the file exists would always hit the file system. This function checks the
opcode cache first.

```php
$config = Config::loadFromScript('tmp/settings.php');

if (!isset($config)) {
    $config = (new Config())
        ->load('composer.json')
        ->load('config/settings.yml');
        
    $config->saveAsScript('tmp/settings.php');
}
```

[500x faster caching]: https://medium.com/@dylanwenzlau/500x-faster-caching-than-redis-memcache-apc-in-php-hhvm-dcd26e8447ad
