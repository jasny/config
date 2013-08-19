<?php

define('CONFIGTEST_SUPPORT_PATH', __DIR__ . '/support');

set_include_path(dirname(__DIR__) . '/src:' . get_include_path());

$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->setUseIncludePath(true);
