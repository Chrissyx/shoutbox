<?php
/**
 * Generic configuration getter and setter for various modules.
 *
 * @author Chrissyx <chris@chrissyx.com>
 * @copyright (c) 2009, 2010 by Chrissyx
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ Creative Commons 3.0 by-nc-sa
 * @package CHS_Core
 * @version 1.0
 */
class CHSConfig implements CHSModule
{
 /**
  * Name of config file to work with.
  *
  * @var string Name of config file
  */
 private $cfgFile;

 /**
  * Contents of the config file as multidimensional array. For each module an own array and therefore section.
  *
  * @var array Contents of the config file
  */
 private $configSets = array();

 /**
  * Detects the config file or creates a new one to work with. Loads all configuration data.
  */
 function __construct()
 {
  if(($this->cfgFile = current(glob(Loader::getDataPath() . '*Config.cfg'))) === false)
  {
   $this->cfgFile = Loader::getDataPath() . md5(time()) . 'Config.cfg';
   if(file_put_contents($this->cfgFile, serialize($this->configSets)) === false)
    exit('<b>ERROR:</b> Can\'t create config file!');
  }
  $this->configSets = unserialize(file_get_contents($this->cfgFile));
 }

 /**
  * Unused.
  *
  * @see CHSCore::execute()
  */
 public function execute()
 {
  trigger_error(__METHOD__ . '(): No need to execute this module. Use the getter and setter functions to manage your config values.', E_USER_NOTICE);
 }

 /**
  * Returns configuration data for stated module.
  *
  * @param string $module Name of module
  * @return array Configuration data or false if no data could be found
  */
 public function getConfigSet($module)
 {
  return $this->hasConfigSet($module) ? $this->configSets[$module] : false;
 }

 /**
  * Returns a single configuration value from an existing set.
  *
  * @param string $module Name of module
  * @param mixed $name Identifier of config value
  * @return
  */
 public function getConfigValue($module, $key)
 {
  return isset($this->configSets[$module][$key]) ? $this->configSets[$module][$key] : false;
 }

 /**
  * Checks availability of configuration data for stated module.
  *
  * @param string $module Name of module
  * @return bool Configuration data is available
  */
 public function hasConfigSet($module)
 {
  return isset($this->configSets[$module]);
 }

 /**
  * Sets configuration data for stated module and updates config file. <b>Existing data will be overwritten!</b>
  *
  * @param string $module Name of module
  * @param array $configSet Configuration data of module
  * @return int|bool Number of bytes that were written to the file or false on failure
  */
 public function setConfigSet($module, $configSet=array())
 {
  $this->configSets[$module] = $configSet;
  return file_put_contents($this->cfgFile, serialize($this->configSets));
 }

 /**
  * Sets a single configuration value of a set or creates a new one with this specific value. <b>Existing data will be overwritten!</b>
  *
  * @param string $module Name of module
  * @param mixed $key Identifier to access the value
  * @param mixed $value Configuration entry
  * @return int|bool Number of bytes that were written to the file or false on failure
  */
 public function setConfigValue($module, $key, $value)
 {
  return $this->setConfigSet($module, $this->hasConfigSet($module) ? array_merge($this->getConfigSet($module), array($key => $value)) : array($key => $value));
 }
}
?>