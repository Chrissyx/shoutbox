<?php
/**
 * Shoutbox module
 *
 * @author Chrissyx <chris@chrissyx.com>
 * @copyright (c) 2006-2022 by Chrissyx
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ Creative Commons 3.0 by-nc-sa
 * @package CHS_Shoutbox
 * @version 2.1
 */
/**
 * Performs all shoutbox actions.
 */
class CHSShoutbox implements CHSModule
{
    /**
     * Loaded configuration set is stored here.
     *
     * @var array Loaded configuration values
     */
    private $config = array();

    /**
     * Cached smilies ready for usage.
     *
     * @var array Prepared smilies for search and replace
     */
    private $smilies = array();

    /**
     * Last used nick name for shouting.
     *
     * @var string Last used nick
     */
    private $shoutNick = '';

    /**
     * Loads the configuration set and smilies.
     *
     * @see CHSConfig::getConfigSet()
     */
    function __construct()
    {
        if(($this->config = Loader::getModule('CHSConfig')->getConfigSet('CHSShoutbox')) === false)
            exit(Loader::getModule('CHSLanguage')->getString('error_no_settings', 'shoutbox', 'CHSShoutbox'));
        if(!Loader::getModule('CHSLanguage')->setModule('CHSShoutbox')) //Set shortcut
            trigger_error(__METHOD__ . '(): Cannot set module name as shortcut', E_USER_WARNING);
        //Cache smilies
        if(!empty($this->config['loc_smilies']))
        {
            if(($this->config['img_path'] = basename($this->config['loc_smilies']) == 'smilies.var' ? implode('/', array_slice(explode('/', $this->config['loc_smilies']), 0, -2)) : '') != '')
                $this->config['img_path'] .= '/';
            array_map(array($this, 'cacheSmiley'), basename($this->config['loc_smilies']) != 'smilies.var' ? array_slice(file($this->config['loc_smilies']), 1) : file($this->config['loc_smilies']));
        }
    }

    /**
     * Prepares and adds a raw smiley data row as a ready-for-use entry with synonym and XHTML img code.
     *
     * @param string $smiley Smiley data row from file
     */
    private function cacheSmiley($smiley)
    {
        $smiley = explode("\t", trim($smiley));
        $this->smilies[$smiley[1]] = '<img src="' . $this->config['img_path'] . (strpos($smiley[2], '/') === false ? $this->config['folder_of_smilies'] : '') . $smiley[2] . '" alt="' . $smiley[1] . '" style="border:none;" />';
    }

    /**
     * Displays shouts from stated content array.
     *
     * @param array $shouts Shouts to display
     * @param bool $limit Limit displaying shouts per page
     */
    private function printShouts($shouts, $limit=false)
    {
        if(empty($shouts))
            echo('<p>' . Loader::getModule('CHSLanguage')->getString('no_shouts', 'shoutbox') . '</p>');
        elseif($limit)
        {
            $curPage = !isset($_GET['page']) ? 0 : ($_GET['page'] < 0 ? 0 : (($_GET['page']*$this->config['shouts_in_archive'] >= ($size = count($shouts))) ? abs($_GET['page']-1) : intval($_GET['page'])));
            $start = $curPage*$this->config['shouts_in_archive'];
            echo($navbar = '<p style="font-size:small; text-align:center;"><a href="' . $_SERVER['PHP_SELF'] . '?module=CHSShoutbox&amp;action=showArchive&amp;page=0">&laquo;</a> ' . sprintf($curPage != 0 ? '<a href="' . $_SERVER['PHP_SELF'] . '?module=CHSShoutbox&amp;action=showArchive&amp;page=' . ($curPage-1) . '">&lsaquo; %s</a>' : '&lsaquo; %s', Loader::getModule('CHSLanguage')->getString('prev_page', 'archive')) . ' &ndash; ' . Loader::getModule('CHSLanguage')->getString('page', 'archive') . ' ' . ($curPage+1) . ' &ndash; ' . sprintf($curPage != floor($size/$this->config['shouts_in_archive']) ? '<a href="' . $_SERVER['PHP_SELF'] . '?module=CHSShoutbox&amp;action=showArchive&amp;page=' . ($curPage+1) . '">%s &rsaquo;</a>' : '%s &rsaquo;', Loader::getModule('CHSLanguage')->getString('next_page', 'archive')) . ' <a href="' . $_SERVER['PHP_SELF'] . '?module=CHSShoutbox&amp;action=showArchive&amp;page=' . floor($size/$this->config['shouts_in_archive']) . '">&raquo;</a></p>');
            for($i=$start,$end=(($size-$start) > $this->config['shouts_in_archive']) ? $start+$this->config['shouts_in_archive'] : $size; $i<$end; $i++)
                $this->printSingleShout($shouts[$i]);
            echo($navbar);
        }
        else
            foreach($shouts as $value)
                $this->printSingleShout($value);
    }

    /**
     * Displays a single shout.
     *
     * @param string $value Shout to display
     */
    private function printSingleShout($value)
    {
        #timestamp - ip - name - shout
        $value = explode("\t", $value);
        $value[3] = preg_replace_callback("/([^ ^>]+?:\/\/|www\.)[^ ^<^\.]+(\.[^ ^<^\.]+)+/si", function($arr)
        {
            return $arr[2] ? '<a href=\"' . ($arr[1] == 'www.' ? 'http://' : '') . $arr[0] . '\" target=\"blank\">' . (strlen($arr[0]) > 25 ? substr($arr[0], 0, 15) . '...' . substr($arr[0], -10) : $arr[0]) . '</a>' : $arr[0];
        }, $value[3]);
        if(($size = preg_match_all("/([\w]{26,})/si", $value[3], $wrapme)) > 0)
        {
            $wrapme = array_shift($wrapme);
            $value[3] = strtr($value[3], array_combine($wrapme, array_map('wordwrap', $wrapme, array_fill(0, $size, 15), array_fill(0, $size, '&shy;'), array_fill(0, $size, true))));
        }
        echo('  <p style="margin-top:4px; margin-bottom:4px;"><strong>' . $value[2] . '</strong> ' . date(Loader::getModule('CHSLanguage')->getString('DATEFORMAT', 'shoutbox'), $value[0]) . ':<br />
            ' . (!empty($this->smilies) ? strtr($value[3], $this->smilies) : $value[3]) . (isset($_SESSION['shoutpw']) ? '<br />
                <span style="font-size:small; white-space:nowrap;">[ ' . Loader::getModule('CHSLanguage')->getString('ip_address', 'shoutbox') . ' <strong>' . $value[1] . '</strong> ] &ndash; [ <a href="' . $_SERVER['PHP_SELF'] . '?module=CHSShoutbox&amp;action=deleteShout&amp;shoutID=' . $value[0] . (isset($_GET['page']) ? '&amp;page=' . $_GET['page'] : '') . '">' . Loader::getModule('CHSLanguage')->getString('delete', 'shoutbox') . '</a> ]</span>' : '') . '</p><hr noshade="noshade" />
        ');
    }

    /**
     * Deletes a single shout, identified by timestamp in $_GET['shoutID'].
     */
    public function deleteShout()
    {
        $archive = file($this->config['loc_archive'], FILE_SKIP_EMPTY_LINES);
        //Shout located in box...
        if(!isset($_GET['page']))
        {
            foreach($shouts = file($this->config['loc_shoutbox'], FILE_SKIP_EMPTY_LINES) as $key => $value)
            {
                $value = explode("\t", $value);
                if($value[0] == (int) $_GET['shoutID'])
                {
                    unset($shouts[$key]);
                    if(!empty($archive))
                        $shouts[] = array_shift($archive);
                    break;
                }
            }
            file_put_contents($this->config['loc_shoutbox'], $shouts, LOCK_EX);
        }
        //...shout located in archive
        else
            foreach($archive as $key => $value)
            {
                $value = explode("\t", $value);
                if($value[0] == (int) $_GET['shoutID'])
                {
                    unset($archive[$key]);
                    break;
                }
            }
        //The archive needs to be updated in (almost) any case
        file_put_contents($this->config['loc_archive'], $archive, LOCK_EX);
        header('Location: ' . $_SERVER['PHP_SELF'] . (isset($_GET['page']) ? '?module=CHSShoutbox&action=showArchive&page=' . $_GET['page'] : ''));
        echo('Shout gelöscht! <a href="' . $_SERVER['PHP_SELF'] . (isset($_GET['page']) ? '?module=CHSShoutbox&amp;action=showArchive&amp;page=' . $_GET['page'] : '') . '">' . Loader::getModule('CHSLanguage')->getString('go_on', 'common') . '</a>');
    }

    /**
     * Prints more aka all available smilies as a separate page.
     */
    public function moreSmilies()
    {
        CHSFunctions::printHead('CHS &ndash; Shoutbox: ' . Loader::getModule('CHSLanguage')->getString('title', 'more'), 'Shoutbox, CHS, ' . Loader::getModule('CHSLanguage')->getString('title', 'more') . ', Chrissyx', Loader::getModule('CHSLanguage')->getString('descr', 'more'), Loader::getModule('CHSLanguage')->getString('charset', 'common'), Loader::getModule('CHSLanguage')->getLangCode());
        $i=0;
        foreach($this->smilies as $key => $value)
        {
            if((++$i % $this->config['smilies_per_row']) == 0)
                echo("<br />\n");
            echo('  <a href="javascript:(sbbox = opener.document.getElementById(\'shoutboxform\').shoutbox).value += \' ' . strtr($key, CHSFunctions::getHTMLJSTransTable()) . '\'; sbbox.focus();">' . $value . "</a>\n");
        }
        CHSFunctions::printTail('CHSShoutbox', 'common');
    }

    /**
     * Displays archived shouts.
     */
    public function showArchive()
    {
        CHSFunctions::printHead('CHS &ndash; Shoutbox: ' . Loader::getModule('CHSLanguage')->getString('title', 'archive'), 'Shoutbox, CHS, ' . Loader::getModule('CHSLanguage')->getString('title', 'archive') . ', Chrissyx', Loader::getModule('CHSLanguage')->getString('descr', 'archive'), Loader::getModule('CHSLanguage')->getString('charset', 'common'), Loader::getModule('CHSLanguage')->getLangCode());
        $this->printShouts(array_map('utf8_encode', file($this->config['loc_archive'])), $this->config['shouts_in_archive']);
        CHSFunctions::printTail('CHSShoutbox', 'common');
    }

    /**
     * Generates and outputs a random CAPTCHA image as PNG. The CAPTCHA string will be discarded.
     */
    public function outputCAPTCHA()
    {
        for($i=0, $string=''; $i<5; $i++)
            $string .= chr(mt_rand(48, 90));
        $captcha = imagecreatetruecolor(40, 20);
        imagestring($captcha, 3, 3, 3, $string, imagecolorallocate($captcha, 255, 0, 0));
        header('Content-Type: image/png');
        imagepng($captcha);
        imagedestroy($captcha);
    }

    /**
     * Displays the most recent shouts and handles shouting action.
     *
     * @see CHSCore::execute()
     */
    public function execute()
    {
        //Prepare shouts
        ($shouts = @file($this->config['loc_shoutbox'], FILE_SKIP_EMPTY_LINES)) !== false or exit(Loader::getModule('CHSLanguage')->getString('error_no_shouts', 'shoutbox'));
        //Perform desired action
        switch(isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : ''))
        {
            case 'shout':
            $this->shoutNick = Loader::getModule('CHSFunctions')->stripEscape(trim($_POST['name']));
            if($this->config['captcha'] && $_POST['captcha'] != Loader::getModule('CHSLanguage')->getString('captcha_word', 'shoutbox'))
                $_POST['captcha'] = 'border-color:#FF0000; ';
            else
            {
                //Add new shout on top
                array_unshift($shouts, time() . "\t" . $_SERVER['REMOTE_ADDR'] . "\t" . str_replace(array("\r", "\n"), '', (!empty($this->shoutNick) ? $this->shoutNick : $_SERVER['REMOTE_ADDR']) . "\t" . nl2br(Loader::getModule('CHSFunctions')->stripEscape(trim($_POST['shoutbox'])))) . "\n");
                if(($i = count($shouts)) > $this->config['amount_of_shouts'])
                {
                    //Move oldest shouts into archive
                    for($archive = file($this->config['loc_archive']); $i>$this->config['amount_of_shouts']; $i--)
                        array_unshift($archive, array_pop($shouts));
                    file_put_contents($this->config['loc_archive'], $archive, LOCK_EX);
                }
                file_put_contents($this->config['loc_shoutbox'], $shouts, LOCK_EX);
                unset($_POST['captcha'], $_POST['shoutbox']);
            }

            default:
?>

<script type="text/javascript">
function canShout()
{
 (sbform = document.getElementById('shoutboxform')).shout.disabled = sbform.name.value.length != 0 ? false : true;
}

function setShoutSmiley(smiley)
{
 (sbbox = document.getElementById('shoutboxform').shoutbox).value += smiley;
 sbbox.focus();
}
</script>

<form id="shoutboxform" name="shoutboxform" action="<?=$_SERVER['PHP_SELF']?>#shoutboxform" method="post" onmouseover="canShout();">
<div style="white-space:nowrap;"><?=Loader::getModule('CHSLanguage')->getString('nick', 'shoutbox')?> <input type="text" name="name" size="17" value="<?=$this->shoutNick?>" onchange="canShout();" /><br />
<textarea name="shoutbox" rows="3" cols="18"><?=$this->config['captcha'] && isset($_POST['shoutbox']) ? trim(Loader::getModule('CHSFunctions')->stripEscape($_POST['shoutbox'])) : ''?></textarea><?php
            if(!empty($this->smilies))
            {
                $i=0;
                foreach($this->smilies as $key => $value)
                {
                    if($i >= $this->config['amount_of_smilies'])
                        break;
                    if(($i++ % $this->config['smilies_per_row']) == 0)
                        echo("<br />\n");
                    echo('<a href="javascript:setShoutSmiley(\' ' . strtr($key, Loader::getModule('CHSFunctions')->getHTMLJSTransTable()) . '\');">' . $value . '</a>');
                }
            }
            if($this->config['captcha'])
                echo('<br />
<input type="text" name="captcha" style="' . (isset($_POST['captcha']) ? $_POST['captcha'] : '') . 'vertical-align:middle; width:35px;" /> &larr; <span style="font-size:small;">' . sprintf(Loader::getModule('CHSLanguage')->getString('captcha_text', 'shoutbox'), Loader::getModule('CHSLanguage')->getString('captcha_word', 'shoutbox')) . '</span><img src="' . $_SERVER['PHP_SELF'] . '?module=CHSShoutbox&amp;action=outputCAPTCHA" alt="CAPTCHA" style="display:none; vertical-align:middle;" />');
?><br />
<input type="submit" name="shout" value="<?=Loader::getModule('CHSLanguage')->getString('shout', 'shoutbox')?>" style="width:53px;" readonly="readonly" /> <input type="reset" value="<?=Loader::getModule('CHSLanguage')->getString('reset', 'shoutbox')?>" style="width:53px;" /> <input type="button" value="<?=Loader::getModule('CHSLanguage')->getString('archive', 'shoutbox')?>" style="width:53px;" onclick="window.open('<?=$_SERVER['PHP_SELF']?>?module=CHSShoutbox&amp;action=showArchive&amp;page=0', '_blank', 'width=400, resizable, scrollbars, status');" /><br />
<input type="button" value="<?=Loader::getModule('CHSLanguage')->getString('reload', 'shoutbox')?>" onclick="window.location = window.location.href.replace(/\#.*$/, '');" style="width:<?=!empty($this->smilies) ? '63px;" /> <input type="button" value="' . Loader::getModule('CHSLanguage')->getString('title', 'more') . '" style="width:100px;" onclick="window.open(\'' . $_SERVER['PHP_SELF'] . '?module=CHSShoutbox&amp;action=moreSmilies\', \'_blank\', \'width=250, resizable, scrollbars, status\')' : '167px'?>;" />
<?=isset($_SESSION['shoutpw']) ? '<br />
<input type="button" value="' . Loader::getModule('CHSLanguage')->getString('title', 'logout') . '" onclick="window.location = \'' . $_SERVER['PHP_SELF'] . '?module=CHSShoutboxAdmin&amp;action=logout\';" style="width:167px;" />' : ''?>
</div>
<input type="hidden" name="action" value="shout" />
</form>

<?php
            //Display shouts
            $this->printShouts($shouts);
            break;
        }
    }

    /**
     * Detects valid action and performs it in a seperate page.
     *
     * @see CHSCore::onLoad()
     */
    public function onLoad()
    {
        if(isset($_GET['module']) && $_GET['module'] == get_class() && in_array(($action = isset($_GET['action']) ? $_GET['action'] : ''), array('deleteShout', 'moreSmilies', 'showArchive', 'outputCAPTCHA')))
        {
            Loader::execute('CHSFunctions');
            exit($this->$action());
        }
    }
}
?>