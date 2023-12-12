<?php
/**
 * Updater for past versions of Shoutbox.
 *
 * @author Chrissyx <chris@chrissyx.com>
 * @copyright (c) 2022-2023 by Chrissyx
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ Creative Commons 3.0 by-nc-sa
 * @package CHS_Shoutbox
 * @version 2.1.2
 */
function checkConvertUtf8($string)
{
    return mb_check_encoding($string, 'UTF-8') ? $string : @utf8_encode($string);
}

echo('Updating...');

if(version_compare(PHP_VERSION, '5.6.0') >= 0 && function_exists('mb_check_encoding'))
{
    require('chscore/CHSCore.php');
    $config = Loader::getModule('CHSConfig')->getConfigSet('CHSShoutbox');
    if($config === false)
        exit(Loader::getModule('CHSLanguage')->getString('error_no_settings', 'shoutbox', 'CHSShoutbox'));
    Loader::getModule('CHSLanguage')->setModule('CHSShoutbox');
    //Update shouts
    $shouts = file($config['loc_shoutbox'], FILE_SKIP_EMPTY_LINES);
    if($shouts === false)
        exit(Loader::getModule('CHSLanguage')->getString('error_no_shouts', 'shoutbox'));
    $shouts = array_map('checkConvertUtf8', $shouts); //Convert each single shout having possible mixed encodings after PHP updates
    file_put_contents($config['loc_shoutbox'], $shouts, LOCK_EX);
    //Update archive
    $shouts = file($config['loc_archive']);
    if($shouts !== false)
    {
        $shouts = array_map('checkConvertUtf8', $shouts); //Convert each single shout having possible mixed encodings after PHP updates
        file_put_contents($config['loc_archive'], $shouts, LOCK_EX);
    }
    //Update smilies
    if($config['loc_smilies'] != '' && basename($config['loc_smilies']) != 'smilies.var')
        file_put_contents($config['loc_smilies'], checkConvertUtf8(file_get_contents($config['loc_smilies'])), LOCK_EX);
}

echo('complete!');
?>