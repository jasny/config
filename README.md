Jasny's Config class
====================

[![Build Status](https://secure.travis-ci.org/jasny/Config.png?branch=master)](http://travis-ci.org/jasny/Config)

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
