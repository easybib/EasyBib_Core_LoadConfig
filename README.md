# EasyBib\_Core\_LoadConfig

[![Build Status](https://secure.travis-ci.org/easybib/EasyBib_Core_LoadConfig.png?branch=master)](http://travis-ci.org/easybib/EasyBib_Core_LoadConfig)

A wrapper around `\Zend_Config_Ini` to load module configuration throughout our application.

We don't run a giant `application.ini` for all settings and instead distribute configuration along with the module in
local `etc` directories. This wrapper helps loading these configuration files whenever needed - on demand.

If no environment is set, `production` is assumed which also caches configuration in [APC](http://pecl.php.net/package/APC).

## Requirements

 * PHP 5.3+
 * Zend Framework (1.11.11+)
 * APC

**Please note:** The only Zend Framework component used is `\Zend_Config_Ini` (and its dependencies).

## Setup

    $ pear channel-discover easybib.github.com/pear
    $ pear install easybib/EasyBib_Core_LoadConfig-alpha

## Example

    <?php
    use Easybib\Core\LoadConfig as ConfigLoader;
    require_once 'EasyBib/Core/LoadConfig.php';

    // load app/etc/config.ini
    $loader = new ConfigLoader('config.ini');
    $config = $loader->load();

In case your application structure is different:

    <?php
    use Easybib\Core\LoadConfig as ConfigLoader;
    require_once 'EasyBib/Core/LoadConfig.php';

    define('APPLICATION_DIR', '/absolute/path/to/the/folder/application');

    // load application/configs/config.ini
    $loader = new ConfigLoader('config.ini');
    $config = $loader->setConfigDir('configs')->load();

For more examples, please check out the test suite!