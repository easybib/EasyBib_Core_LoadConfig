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
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://www.easybib.com
 * @link     http://github.com/easybib/EasyBib_Core_LoadConfig
 */
class LoadConfig
{
    /**
     * The environment.
     * @var string
     */
    protected $environment = 'production';

    /**
     * Configuration file to load.
     * @var string
     */
    protected $file;

    /**
     * Name of the module to load it from.
     * @var string
     */
    protected $module;

    /**
     * The directory where configuration files are located.
     * @var string
     */
    protected $configDir = 'etc';

    /**
     * @param string $file   The configuration file, e.g. redis.ini.
     * @param string $module The module to load it from.
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
     * Load the config.
     *
     * @return \Zend_Config_Ini
     * @throws \RuntimeException When stuff goes wrong.
     */
    public function load()
    {
        static $path = null;
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
        if ($this->environment == 'production') {
            $config = \apc_fetch($apcKey, $success);
        } else {
            $success = false;
        }
        if ($success === true
            && $this->environment == 'production'
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
            if ($this->environment == 'production') {
                apc_store($apcKey, $config);
            }
            return $config;
        } catch (\Zend_Exception $e) {
            // handle
            throw new \RuntimeException("Problem? >> {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Save, in case the instance/config gets altered, we can save it into the APC
     * backend.
     *
     * @param \Zend_Config_Ini $config
     *
     * @return $this
     * @uses   self::generateKey()
     */
    public function save(\Zend_Config_Ini $config)
    {
        if ($this->environment == 'production') {
            \apc_store($this->generateKey(), $config);
        }
        return $this;
    }

    /**
     * Set the configuration directory (some use 'etc', some use 'configs')
     *
     * @param string $directory
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
     * @param string $environment
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
     *
     *
     * @param string
     * 
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     *
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
     *
     *
     * @param string
     * 
     * @return string
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
        if ($this->module == 'default') {
            $path .= '/' . $this->configDir;
        } else {
            $path .= sprintf(
                '/modules/%s/%s',
                $this->module,
                $this->configDir
            );
        }
        return sprintf('%s/%s', $path, $this->file);
    }
}
