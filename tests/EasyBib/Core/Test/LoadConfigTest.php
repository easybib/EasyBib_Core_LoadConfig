<?php
/**
 * EasyBib Copyright 2008-2012
 *
 * PHP Version 5.3
 *
 * @category Testing
 * @package  LoadConfig
 * @author   Till Klampaeckel <till@lagged.biz>
 * @license  http://www.easybib.com/company/terms Terms of Service
 * @version  GIT: $Id$
 * @link     http://www.easybib.com
 * @link     http://github.com/easybib/EasyBib_Core_LoadConfig
 */
namespace EasyBib\Core\Test;

use EasyBib\Core\LoadConfig;

/**
 * LoadConfigTest
 *
 * @category Testing
 * @package  LoadConfig
 * @author   Till Klampaeckel <till@lagged.biz>
 * @license  http://www.easybib.com/company/terms Terms of Service
 * @version  Release: @package_version@
 * @link     http://www.easybib.com
 * @link     http://github.com/easybib/EasyBib_Core_LoadConfig
 */
class LoadConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * To be used in tests!
     * @var LoadConfig
     */
    protected $ezConfig;

    protected function setUp()
    {
        $this->ezConfig = new LoadConfig('foo');
        $this->ezConfig->setEnvironment('testing');
    }

    protected function tearDown()
    {
        unset($this->ezConfig);
    }

    /**
     * Load  configuration from 'app/modules/bar/configs/redis.ini'.
     *
     * @return void
     */
    public function testRedisConf()
    {
        $this->assertInstanceOf('EasyBib\Core\LoadConfig', $this->ezConfig->setConfigDir('configs'));
        $this->assertInstanceOf('EasyBib\Core\LoadConfig', $this->ezConfig->setFile('redis.ini'));
        $this->assertInstanceOf('EasyBib\Core\LoadConfig', $this->ezConfig->setModule('bar'));
        $this->assertInstanceOf('EasyBib\Core\LoadConfig', $this->ezConfig->setEnvironment('testing'));

        $redisConfig = $this->ezConfig->load();
        $this->assertEquals('127.0.0.1', $redisConfig->host);
        $this->assertEquals('31337', $redisConfig->port);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testUnknownConfigThrowsException()
    {
        $config = $this->ezConfig->load();
        $this->assertFalse($config);
    }

    /**
     * Load app/etc/couchdb.ini
     *
     * @return void
     */
    public function testDefault()
    {
        $config = $this->ezConfig->setEnvironment('testing')->setFile('couchdb.ini')->load();
        $this->assertInstanceOf('\Zend_Config_Ini', $config);

        $this->assertEquals('bigcouch', $config->host);
        $this->assertEquals('http', $config->scheme);
        $this->assertEquals('5784', $config->port);
    }

    /**
     * Load impexport/etc/config.ini
     */
    public function testModule()
    {
        $this->ezConfig->setFile('config.ini');
        $this->ezConfig->setModule('foo');

        $config = $this->ezConfig->load();

        $this->assertInstanceOf('Zend_Config_Ini', $config);
    }

    public function testProduction()
    {
        if (!extension_loaded('apc')) {
            $this->markTestSkipped("Test requires ext/apc.");
            return;
        }
        $this->ezConfig->setEnvironment('production');
        $this->ezConfig->setFile('config.ini');
        $this->ezConfig->setModule('foo');

        $key = $this->ezConfig->getApcKey();
        $this->assertNotEmpty($key);
        \apc_delete($key);

        $config = $this->ezConfig->load();

        $this->assertInstanceOf('Zend_Config_Ini', $config);
    }

    /**
     * Test {@link EasyBib\Core\LoadConfig::save()
     */
    public function testProductionAndAlter()
    {
        if (!extension_loaded('apc')) {
            $this->markTestSkipped("Test requires ext/apc.");
            return;
        }
        $this->ezConfig->setEnvironment('production');
        $this->ezConfig->setFile('config.ini');
        $this->ezConfig->setModule('foo');

        $key = $this->ezConfig->getApcKey();
        \apc_delete($key);

        $config = $this->ezConfig->load();
        $config->foo = 'bar';

        $this->assertInstanceOf('EasyBib\Core\LoadConfig', $this->ezConfig->save($config));
    }

    /**
     * make sure the key generated is not just based on 'filename'
     *
     * @return void
     * @see    LoadConfig::generateKey()
     */
    public function testKeyGeneration()
    {
        $this->ezConfig->setFile('config.ini');

        $key1 = $this->ezConfig->getApcKey();

        $this->ezConfig->setModule('wat');

        $key2 = $this->ezConfig->getApcKey();

        $this->assertNotEquals($key1, $key2);

        $this->ezConfig->setModule('default');

        $key3 = $this->ezConfig->getApcKey();

        $this->assertEquals($key1, $key3);
    }
}
