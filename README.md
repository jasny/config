Jasny's Config class
====================

[![Build Status](https://secure.travis-ci.org/jasny/config.png?branch=master)](http://travis-ci.org/jasny/config)
[![Coverage Status](https://coveralls.io/repos/jasny/config/badge.svg?branch=master&service=github)](https://coveralls.io/github/jasny/config?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/9d9ee2fe-1622-4883-b46e-aa21b14a3931/mini.png)](https://insight.sensiolabs.com/projects/9d9ee2fe-1622-4883-b46e-aa21b14a3931)

Configure your application. You can load .ini, .json and .yaml files or MySQL DB.

## Installation ##

Jasny Config is registred at packagist as [jasny/config](https://packagist.org/packages/jasny/config) and can be
easily installed using [composer](http://getcomposer.org/). Alternatively you can simply download the .zip and copy
the file from the 'src' folder.

## Example ##

    <?php
        use Jasny\Config;

        Config::i()->load('settings.yaml');
        Config::i()->db = new Config('db.ini');
        Config::i()->env = ($tmp = getenv('APPLICATION_ENV')) ? $tmp : 'prod';
        Config::i()->load("$env.yaml", array('optional'=>true));


## API documentation (generated) ##

http://jasny.github.com/Config/docs

