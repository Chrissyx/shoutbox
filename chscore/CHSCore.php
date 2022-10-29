<?php
/**
 * Loads the core and stores it in the session for further accessing.
 * Dubbed &quot;LeetCore&quot;^^
 *
 * @author Chrissyx <chris@chrissyx.com>
 * @copyright (c) 2009-2022 by Chrissyx
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ Creative Commons 3.0 by-nc-sa
 * @package CHS_Core
 * @version 1.1
 */
/**
 * Interface template for every implementing module. Don't forget an onLoad() function by registering for that event.
 *
 * @package CHS_Core
 */
interface CHSModule
{
    /**
     * Executes this module.
     *
     * @see CHSCore::execute()
     */
    public function execute();
}

/**
 * The module manager.
 *
 * @package CHS_Core
 */
class CHSCore
{
    /**
     * Detected modules are stored here.
     *
     * @see hasModule()
     * @var array Available modules
     */
    private $availableModules = array();

    /**
     * Relative path to directory of additional config files.
     *
     * @var string Path to configurations
     */
    private $configPath;

    /**
     * Relative path to directory of data files.
     *
     * @var string Path to data
     */
    private $dataPath;

    /**
     * Relative path to directory of images.
     *
     * @var string Path to images
     */
    private $imagePath;

    /**
     * Relative path to directory of language files.
     *
     * @var string Path to languages
     */
    private $langPath;

    /**
     * Loaded modules are stored here after first execution.
     *
     * @see CHSCore::getModule()
     * @var array Created modules
     */
    private $loadedModules = array();

    /**
     * Relative path to directory of modules.
     *
     * @var string Path to modules
     */
    private $modulesPath;

    /**
     * Modules registered for onLoad action are stored here.
     *
     * @see CHSCore::onLoad()
     * @var array Modules to be notifed
     */
    private $onLoadModules = array();

    /**
     * Detects paths and available modules to execute.
     */
    function __construct()
    {
        $this->configPath = ($rootDir = basename(rtrim(dirname(__FILE__), DIRECTORY_SEPARATOR)) . '/') . 'config/';
        $this->dataPath = $rootDir . 'data/';
        $this->imagePath = $rootDir . 'images/';
        $this->langPath = $rootDir . 'languages/';
        $this->modulesPath = $rootDir . 'modules/';
        foreach(glob($this->modulesPath . '*.php') as $value)
        {
            $this->availableModules[] = $curModule = basename($value, '.php');
            //Check for onLoad modules
            if(file_exists($this->configPath . $curModule . '.ini'))
                if(@key($curConfig = parse_ini_file($this->configPath . $curModule . '.ini')) == 'notifyOnLoad' && (bool) $curConfig['notifyOnLoad'])
                    $this->onLoadModules[] = $curModule;
        }
    }

    /**
     * Backs up loaded modules in serialized form.
     */
    function __destruct()
    {
        foreach(array_keys($this->loadedModules) as $value)
            if(is_object($this->loadedModules[$value]['class']))
                $this->loadedModules[$value]['class'] = serialize($this->loadedModules[$value]['class']);
    }

    /**
     * Loads and executes the stated module. Triggers an error if module could not be found or does not implement the needed interface.
     *
     * @param string $module The module to load and execute
     * @see CHSModule::execute()
     * @see getModule()
     * @return mixed Boolean ONLY if an error occured and state of its report. The loaded class otherweise.
     */
    public function execute($module)
    {
        $this->getModule($module)->execute();
    }

    /**
     * Returns relative path to directory of config files.
     *
     * @return string Path to config folder
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }

    /**
     * Returns relative path to directory of data files.
     *
     * @return string Path to data folder
     */
    public function getDataPath()
    {
        return $this->dataPath;
    }

    /**
     * Returns relative path to directory of images.
     *
     * @return string Path to image folder
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * Returns relative path to directory of language files.
     *
     * @return string Path to language folder
     */
    public function getLangPath()
    {
        return $this->langPath;
    }

    /**
     * Loads the stated module. Triggers an error if module could not be found or does not implement the needed interface.
     *
     * @param string $module The module to load
     * @return mixed The loaded class or false on failure.
     */
    public function &getModule($module)
    {
        //Module is not loaded at all
        if(!isset($this->loadedModules[$module]))
        {
            if(!$this->hasModule($module))
                return !trigger_error(__METHOD__ . '(' . str_replace('&', '&amp;', $module) . '): module does not exist', E_USER_WARNING);
            $this->loadedModules[$module]['eval'] = trim(php_strip_whitespace($this->modulesPath . $module . '.php'), '<?ph>'); //Get *pure* code
            eval($this->loadedModules[$module]['eval']);
            if(!class_exists($module, false))
                return !trigger_error(__METHOD__ . '(' . str_replace('&', '&amp;', $module) . '): module is not a valid class', E_USER_WARNING);
            $class = new $module;
            if(!$class instanceof CHSModule)
                return !trigger_error(__METHOD__ . '(' . str_replace('&', '&amp;', $module) . '): module does not support needed interface', E_USER_WARNING);
            $this->loadedModules[$module]['class'] = $class;
        }
        //Module was loaded before and serialized, restore it back by letting the script engine know the original class structure
        elseif(is_string($this->loadedModules[$module]['class']))
        {
            eval($this->loadedModules[$module]['eval']); //Prevents any needed __autoload() action :)
            $this->loadedModules[$module]['class'] = unserialize($this->loadedModules[$module]['class']);
        }
        //Finally return prepared module
        return $this->loadedModules[$module]['class'];
    }

    /**
     * Returns relative path to directory of modules.
     *
     * @return string Path to modules folder
     */
    public function getModulesPath()
    {
        return $this->modulesPath;
    }

    /**
     * Checks availability of a module.
     *
     * @param string $module Name of module
     * @return bool Module is available for loading and execution
     */
    public function hasModule($module)
    {
        return in_array($module, $this->availableModules);
    }

    /**
     * Notifies registered modules about starting output of HTML
     */
    public function onLoad()
    {
        foreach($this->onLoadModules as $curModule)
            $this->getModule($curModule)->onLoad();
    }
}

/**
 * Gateway to access the core for modules and their execution.
 *
 * @package CHS_Core
 * @see CHSCore
 */
class Loader
{
    /**
     * Executes the stated module.
     *
     * @param string $module Name of module
     * @see CHSCore::execute()
     */
    public static function execute($module)
    {
        $_SESSION['CHSCore']->execute((string) $module);
    }

    /**
     * Returns relative path to directory of config files.
     *
     * @return string Path to config folder
     * @see CHSCore::getConfigPath()
     */
    public static function getConfigPath()
    {
        return $_SESSION['CHSCore']->getConfigPath();
    }

    /**
     * Returns relative path to directory of data files.
     *
     * @return string Path to data folder
     * @see CHSCore::getDataPath()
     */
    public static function getDataPath()
    {
        return $_SESSION['CHSCore']->getDataPath();
    }

    /**
     * Returns relative path to directory of images.
     *
     * @return string Path to image folder
     * @see CHSCore::getImagePath()
     */
    public static function getImagesPath()
    {
        return $_SESSION['CHSCore']->getImagePath();
    }

    /**
     * Returns relative path to directory of language files.
     *
     * @return string Path to language folder
     * @see CHSCore::getLangPath()
     */
    public static function getLangPath()
    {
        return $_SESSION['CHSCore']->getLangPath();
    }

    /**
     * Returns the stated module.
     *
     * @param string $module Name of module
     * @return Loaded module class
     * @see CHSCore::getModule()
     */
    public static function &getModule($module)
    {
        return $_SESSION['CHSCore']->getModule((string) $module);
    }

    /**
     * Returns relative path to directory of modules.
     *
     * @return string Path to modules folder
     * @see CHSCore::getModulesPath()
     */
    public static function getModulesPath()
    {
        return $_SESSION['CHSCore']->getModulesPath();
    }
}

//Start session and core if needed
if(!isset($_SESSION))
    session_start();
if(!isset($_SESSION['CHSCore']))
    $_SESSION['CHSCore'] = new CHSCore;
//Notify regged modules about starting output of HTML
$_SESSION['CHSCore']->onLoad();
?>