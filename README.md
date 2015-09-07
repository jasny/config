Jasny's Config class
====================

[![Build Status](https://secure.travis-ci.org/jasny/config.png?branch=master)](http://travis-ci.org/jasny/config)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/config/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/config/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/config/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/config/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/9d9ee2fe-1622-4883-b46e-aa21b14a3931/mini.png)](https://insight.sensiolabs.com/projects/9d9ee2fe-1622-4883-b46e-aa21b14a3931)

Configure your application. You can load .ini, .json and .yaml files or MySQL DB.

## Installation ##

Jasny Config is registred at packagist as [jasny/config](https://packagist.org/packages/jasny/config) and can be
easily installed using [composer](http://getcomposer.org/).

    composer require jasny/config

## Example ##

```php
use Jasny\Config;

$env = getenv('APPLICATION_ENV') ?: 'prod';

Config::i()->load('settings.yaml');
Config::i()->db = new Config('db.ini');
Config::i()->env = $env;
Config::i()->load("$env.yaml", array('optional'=>true));
```

