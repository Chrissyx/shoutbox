<?php
/**
 * Detects, parses and caches language strings for each module.
 *
 * @author Chrissyx <chris@chrissyx.com>
 * @copyright (c) 2010 by Chrissyx
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ Creative Commons 3.0 by-nc-sa
 * @package CHS_Core
 * @version 1.0
 */
class CHSLanguage implements CHSModule
{
 /**
  * The most fitting language code for each localized module based on user's preference.
  *
  * @var array Assigned language code to each module
  */
 private $assignedModLangCodes = array();

 /**
  * Names of detected language files.
  * 
  * @var array Language files
  */
 private $availableLangFiles = array();

 /**
  * Detected modules with localization.
  *
  * @var array Available modules
  */
 private $availableModules = array();

 /**
  * Name of current module to get strings from.
  * 
  * @var string Name of module
  * @see setModule()
  */
 private $curModule;

 /**
  * Cached and parsed language strings.
  *
  * @see getString()
  * @var array Language strings
  */
 private $languageStrings = array();

 /**
  * Detects available localizations for modules and chooses the best one based on detected user's preference.
  */
 function __construct()
 {
  foreach(glob(Loader::getLangPath() . '*.ini') as $value)
  {
   $curModule = @end(explode('.', ($this->availableLangFiles[] = basename($value, '.ini'))));
   if(!in_array($curModule, $this->availableModules))
    $this->availableModules[] = $curModule;
  }
  $prefLangs = $this->getPrefLangs();
  foreach($this->availableModules as $curModule)
   //For each module detect best available language based on user's preference
   $this->setPrefLang($curModule, $prefLangs);
 }

 /**
  * Unused.
  *
  * @see CHSCore::execute()
  */
 public function execute()
 {
  trigger_error(__METHOD__ . '(): No need to execute this module. Use the getter and setter functions to manage your language strings.', E_USER_NOTICE);
 }

 /**
  * Detects preferred languages of current user reported by its browser.
  *
  * @return array Preferred language codes from current browser, sorted by priority
  * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
  */
 private function getPrefLangs()
 {
  $prefLangs = array();
  foreach(explode(',', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])) as $value) #de-de,de;q=0.8,en-us;q=0.5,en;q=0.3
   $prefLangs[(count($value = explode(';', $value)) == 1 || !preg_match('/q=([\d.]+)/i', $value[1], $quality) ? '1.0' : $quality[1]) . mt_rand(0, 9999)] = $value[0];
  krsort($prefLangs);
  return array_map(create_function('$code', 'return strpos($code, \'-\') === false ? $code : substr($code, 0, 3) . strtoupper(substr($code, 3));'), array_values($prefLangs));
 }

 /**
  * Returns current set language for stated module. Name of module might be optional, if set before via {@link setModule()}.
  *
  * @param string $module Name of module
  * @return string|bool Language code of false if module was not found
  */
 public function getLangCode($module=null)
 {
  return isset($this->assignedModLangCodes[($module = empty($module) ? $this->curModule : $module)]) ? $this->assignedModLangCodes[$module] : false;
 }

 /**
  * Returns available translationa for stated module as language codes. Name of module might be optional, if set before via {@link setModule()}.
  *
  * @param string $module Name of module to check
  * @return array List of available languages
  */
 public function getLangCodes($module=null)
 {
  return array_map('basename', ($codes = glob(Loader::getLangPath() . '*.' . ($module = empty($module) ? $this->curModule : $module) . '.ini')), array_fill(0, count($codes), '.' . $module . '.ini'));
 }

 /**
  * Returns a translated language string for stated index (and section, if needed). Name of module might be optional, if set before via {@link setModule()}.
  * Automatically parses a needed module.
  *
  * @param string $index Identifier of translated string
  * @param string $section Optional name of section in INI file
  * @param string $module Name of module
  * @param string $code Optional language code
  * @return string|bool Localized string or false if identifier was not found
  * @see setModule()
  */
 public function getString($index, $section=null, $module=null, $code=null)
 {
  if(!$this->hasModuleLang(($module = empty($module) ? $this->curModule : $module), ($code = empty($code) ? $this->assignedModLangCodes[$module] : $code)) || (!isset($this->languageStrings[$code][$module]) && !$this->parseFile($module, $code)))
   return !trigger_error(__METHOD__ . '(' . $index . ', ' . $section . ', ' . $module . ', ' . $code . '): Identifier not found', E_USER_NOTICE);
  return isset($this->languageStrings[$code][$module][$section][$index]) ? $this->languageStrings[$code][$module][$section][$index] : (isset($this->languageStrings[$code][$module][$index]) ? $this->languageStrings[$code][$module][$index] : false);
 }

 /**
  * Checks if stated module has any localization.
  *
  * @param string $module Name of module
  * @return bool Any localization is available.
  */
 public function hasModule($module)
 {
  return in_array($module, $this->availableModules);
 }

 /**
  * Checks if stated module has a language file.
  *
  * @param string $module Name of module
  * @param string $code Language code of file to check with
  * @return bool Localization strings are available to parse and use
  */
 public function hasModuleLang($module, $code)
 {
  return in_array($code . '.' . $module, $this->availableLangFiles);
 }

 /**
  * Parses a language file and adds its contents to cached strings. Default language code will be used if no one is specified.
  *
  * @param string $module Name of module
  * @param string $code Optional language code
  * @return bool Result of operation
  */
 private function parseFile($module, $code=null)
 {
  if(!$this->hasModule($module))
   return !trigger_error(__METHOD__ . '(' . $module . ', ' . $code . '): module does not exist', E_USER_WARNING);
  if(!empty($code) && !$this->hasModuleLang($module, $code))
   return !trigger_error(__METHOD__ . '(' . $module . ', ' . $code . '): language code for module does not exist', E_USER_WARNING);
  //Already parsed?
  if(isset($this->languageStrings[($code = empty($code) ? $this->assignedModLangCodes[$module] : $code)][$module]))
   return true;
  //Parse file and add to strings
  foreach(parse_ini_file(Loader::getLangPath() . $code . '.' . $module . '.ini', true) as $sec => $values)
   if(is_array($values)) //INI file has [section]s?
    foreach($values as $key => $value)
     $this->languageStrings[$code][$module][$sec][$key] = $value;
   else
    $this->languageStrings[$code][$module][$sec] = $values;
  return true;
 }

 /**
  * Sets stated language code for certain module. This overwrites the automatically detected code of the module or restores it in case of no code was given.
  * There is usually no need to change this.
  *
  * @param string $code New language code
  * @param string $module Name of module
  * @return bool New code for module accepted and set
 */
 public function setLangCode($code=null, $module=null)
 {
  if(empty($module))
   $module = $this->curModule;
  if(empty($code))
   $this->setPrefLang($module);
  else
  {
   if(!$this->hasModuleLang($module, $code))
    return false;
   $this->assignedModLangCodes[$module] = $code;
  }
  return true;
 }

 /**
  * Sets name of module to use for getting language strings as a shortcut function.
  *
  * @param string $module Name of module
  * @return bool New module accepted
  */
 public function setModule($module)
 {
  if(!$this->hasModule($module))
   return false;
  $this->curModule = $module;
  return true;
 }

 /**
  * Sets most fitting language for stated module or native one on no matching.
  *
  * @param string $module Name of module
  * @param array $prefLangs Preferred language codes, sorted by priority
  */
 private function setPrefLang($module, $prefLangs=null)
 {
  foreach(($prefLangs = empty($prefLangs) ? $this->getPrefLangs() : $prefLangs) as $curPrefLang)
   if(file_exists(Loader::getLangPath() . $curPrefLang . '.' . $module . '.ini'))
   {
    $this->assignedModLangCodes[$module] = $curPrefLang;
    return;
   }
  $this->assignedModLangCodes[$module] = null; //Unset possible old code, explicitly set before
  if(!isset($this->assignedModLangCodes[$module]))
  {
   //Second attempt to detect language at a more general / less strict matching level, e.g. "de-DE" is valid if only "de" was detected
   foreach($prefLangs as $curPrefLang)
    foreach(glob(Loader::getLangPath() . $curPrefLang . '*.' . $module . '.ini') as $value)
    {
     $this->assignedModLangCodes[$module] = basename($value, '.' . $module . '.ini');
     return;
    }
   //No match: Set native code
   if(!isset($this->assignedModLangCodes[$module]))
    $this->assignedModLangCodes[$module] = basename(current(glob(Loader::getLangPath() . '*.' . $module . '.ini')), '.' . $module . '.ini');
  }
 }
}
?>