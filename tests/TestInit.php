<?php
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
    echo "Please run 'php composer.phar install'" . PHP_EOL;
    exit(1);
}
require_once $autoloader;

/**
 * @desc Define APPLICATION_PATH and point to fixtures!
 */
define('APPLICATION_PATH', __DIR__ . '/fixtures/app');
