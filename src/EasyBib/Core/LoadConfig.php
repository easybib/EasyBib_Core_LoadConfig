<?php
/**
 * EasyBib Copyright 2008-2012
 *
 * PHP Version 5.3
 *
 * @category Configuration
 * @package  LoadConfig
 * @author   Till Klampaeckel <till@lagged.biz>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version  GIT: $Id$
 * @link     http://www.easybib.com
 * @link     http://github.com/easybib/EasyBib_Core_LoadConfig
 */
namespace EasyBib\Core;

/**
 * LoadConfig
 *
 * An attempt to refactor and centralize .ini file loading.
 *
 * @category Configuration
 * @package  LoadConfig
 * @author   Till Klampaeckel <till@lagged.biz>
 * @author   Darshan Somashekar <darshan@imagineeasy.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://www.easybib.com
 * @link     http://github.com/easybib/EasyBib_Core_LoadConfig
 */
class LoadConfig
{
    /**
     *  @var string Default production
     */
    protected $environment = 'production';

    /**
     * @var string Configuration file name to load.
     */
    protected $file;

    /**
     * @var string Name of application module with config file.
     */
    protected $module;

    /**
     * @var string The directory where module config files are located.
     */
    protected $configDir = 'etc';

    /**
     * @param string $file   The configuration file, e.g. redis.ini.
     * @param string $module Default = 'default' Application module.
     *
     * @return \Easybib\Core\LoadConfig
     */
    public function __construct($file, $module = 'default')
    {
        $this->file   = $file;
        $this->module = $module;
    }

    /**
     * Delete configuration.
     *
     * @return \EasyBib\Core\LoadConfig
     */
    public function expire()
    {
        if ($this->environment !== 'production') {
            return $this;
        }
        \apc_delete($this->generateKey());
        return $this;
    }

    /**
     * Primarily for testing.
     *
     * @return string
     * @uses   self::generateKey()
     */
    public function getApcKey()
    {
        return $this->generateKey();
    }

    /**
     * Sets path & reads config file.
     *
     * @param string $path Default null. Dir of config file
     *
     * @return \Zend_Config_Ini
     * @throws \RuntimeException When stuff goes wrong.
     */
    public function load($path = null)
    {
        if ($path === null) {
            if (defined('APPLICATION_PATH')) {
                $path = APPLICATION_PATH;
            } else {
                $path = dirname(dirname(dirname(__DIR__))) . '/app';
            }
        }

        $apcKey = $this->generateKey();

        /**
         * @desc In all environments but 'production', we assume false.
         */
        if ($this->environment === 'production') {
            $success = false;   //suppress uninit warnings
            $config = \apc_fetch($apcKey, $success);
        } else {
            $success = false;
        }
        if ($success === true
            && $this->environment === 'production'
            && ($config instanceof \Zend_Config_Ini)
        ) {
            return $config;
        }

        $file = $path . $this->getFSpath();

        try {
            $config = new \Zend_Config_Ini(
                $file,
                $this->environment,
                array('allowModifications' => true)
            );
            if ($this->environment === 'production') {
                apc_store($apcKey, $config);
            }
            return $config;
        } catch (\Zend_Exception $e) {
            // handle
            throw new \RuntimeException("Problem? >> {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Save, in case the instance/config gets altered, we can save it into the
     * APC backend.
     *
     * @param \Zend_Config_Ini $config
     *
     * @return $this
     * @uses   self::generateKey()
     */
    public function save(\Zend_Config_Ini $config)
    {
        if ($this->environment === 'production') {
            \apc_store($this->generateKey(), $config);
        }
        return $this;
    }

    /**
     * Set the config file directory (some use 'etc', some use 'configs')
     *
     * @param string $directory Default 'etc'
     *
     * @return $this
     */
    public function setConfigDir($directory = 'etc')
    {
        if (empty($directory)) {
            throw new \InvalidArgumentException("Directory cannot be empty.");
        }
        $this->configDir = $directory;
        return $this;
    }

    /**
     * Set the environment for the config file.
     *
     * @param string $environment production | development | testing
     *
     * @return $this
     * @throws InvalidArgumentException on empty environment
     */
    public function setEnvironment($environment)
    {
        if (empty($environment)) {
            throw new \InvalidArgumentException("Environment cannot be empty.");
        }
        $this->environment = $environment;
        return $this;
    }

    /**
     * Set config file name
     *
     * @param string filename
     *
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Modify module set in constructor
     *
     * @param string
     *
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    /**
     * Unique key for APC cache
     *
     * @return string key
     */
    protected function generateKey()
    {
        return 'config:' . $this->module . ':' . md5($this->file);
    }

    /**
     * This assumes that all configuration files are always located in
     * app/modules/<module>/etc.
     *
     * @return string
     */
    protected function getFSpath()
    {
        $path = '';
        if ($this->module === 'default') {
            $path .= '/' . $this->configDir;
            //return '/' . $this->configDir . '/' . $this->file;    //new coding style under consideration
        } else {
            //return '/modules/' . $this->module . '/' . $this->configDir . '/' . $this->file;    //new coding style under consideration
            $path .= sprintf(
                '/modules/%s/%s',
                $this->module,
                $this->configDir
            );
        }
        return sprintf('%s/%s', $path, $this->file);
    }
}
