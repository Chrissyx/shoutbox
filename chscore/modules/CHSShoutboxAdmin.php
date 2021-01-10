<?php
/**
 * Admin module for installing and managing the shoutbox.
 *
 * @author Chrissyx <chris@chrissyx.com>
 * @copyright (c) 2006 - 2010 by Chrissyx
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ Creative Commons 3.0 by-nc-sa
 * @package CHS_Shoutbox
 * @version 2.0
 */
/**
 * Installs and manages the shoutbox.
 *
 * @package CHS_Shoutbox
 */
class CHSShoutboxAdmin implements CHSModule
{
 /**
  * Current performed action.
  *
  * @var string Action identifier
  */
 private $action;

 /**
  * Reference to the {@link CHSLanguage} module.
  *
  * @var CHSLanguage {@link CHSLanguage} module
  */
 private $chsLanguage;

 /**
  * Current file containing the hashed password.
  *
  * @var string Name of password file
  */
 private $curPassFile;

 /**
  * Hashed current user password to access counter ACP.
  *
  * @var string|bool Stored hashed user password to compare with.
  */
 private $curPassHash;

 /**
  * Hash of a possible new requested password, ready to replace current one.
  *
  * @var string|bool New hashed password
  */
 private $newPassHash;

 /**
  * Sets reference to language module.
  */
 function __construct()
 {
  $this->chsLanguage = Loader::getModule('CHSLanguage');
 }

 /**
  * Performs the desired action.
  *
  * @see CHSCore::execute()
  */
 public function execute()
 {
  switch($this->action)
  {
# Login #
   case 'login':
   if(isset($_POST['shoutpw']))
   {
    //Check for new pass first
    if($this->newPassHash && CHSFunctions::getHash($_POST['shoutpw']) == $this->newPassHash)
    {
     $this->curPassHash = $this->newPassHash;
     CHSFunctions::setPHPDataFile(substr($this->curPassFile, 0, -4), $this->newPassHash);
    }
    //Check normal pass now
    if(CHSFunctions::getHash($_POST['shoutpw']) == $this->curPassHash)
    {
     $_SESSION['shoutpw'] = CHSFunctions::getHash($_POST['shoutpw']);
     unset($_POST['shoutpw']);
     @header('Location: ' . $_SERVER['PHP_SELF'] . (!empty($_POST['edit']) ? '?module=' . $_POST['edit']  . '&action=admin' : ''));
     exit($this->chsLanguage->getString('logged_in', 'login') . ' <a href="' . $_SERVER['PHP_SELF'] . (!empty($_POST['edit']) ? '?module=' . $_POST['edit'] . '&amp;action=admin' : '') . '">' . $this->chsLanguage->getString('go_on', 'common') . '</a>');
    }
   }
   CHSFunctions::printHead('CHS &ndash; Shoutbox: ' . $this->chsLanguage->getString('title', 'login'), 'Shoutbox, CHS, ' . $this->chsLanguage->getString('title', 'login') . ', Chrissyx', $this->chsLanguage->getString('descr', 'login'), $this->chsLanguage->getString('charset', 'common'), $this->chsLanguage->getLangCode());
   if(isset($_POST['shoutpw']))
    echo(CHSFunctions::getMsgBox($this->chsLanguage->getString('wrong_pass', 'login'), 'red'));
?>

  <h3>CHS &ndash; Shoutbox: <?=$this->chsLanguage->getString('title', 'login')?></h3>
  <form action="<?=$_SERVER['PHP_SELF']?>?module=CHSShoutboxAdmin" method="post">
  <?=$this->chsLanguage->getString('enter_pass', 'login')?> <input type="password" name="shoutpw" <?=(isset($_POST['shoutpw']) ? 'style="border-color:#FF0000;" ' : '') . '/> ' . CHSFunctions::getFont(1) . '(' . $this->chsLanguage->getString('forgotten', 'login') . ' <a href="' . $_SERVER['PHP_SELF']?>?module=CHSShoutboxAdmin&amp;action=newpass"><?=$this->chsLanguage->getString('request_new_pass', 'login')?></a>)</span><br />
  <input type="radio" name="edit" id="editShouts" value=""<?=!isset($_POST['edit']) || (isset($_POST['edit']) && empty($_POST['edit'])) ? ' checked="checked"' : ''?> /><label for="editShouts"><?=$this->chsLanguage->getString('manage_shouts', 'login')?></label><br />
  <input type="radio" name="edit" id="editBox" value="CHSShoutboxAdmin"<?=isset($_POST['edit']) && $_POST['edit'] == 'CHSShoutboxAdmin' ? ' checked="checked"' : ''?> /><label for="editBox"><?=$this->chsLanguage->getString('manage_box', 'login')?></label><br />
  <input type="submit" value="<?=$this->chsLanguage->getString('title', 'login')?>" />
  <input type="hidden" name="action" value="login" />
  </form>

<?php
   CHSFunctions::printTail('CHSShoutbox', 'common');
   break;

# Administration #
   case 'admin':
   if(!isset($_SESSION['shoutpw']) || $_SESSION['shoutpw'] != $this->curPassHash)
    exit($this->chsLanguage->getString('error_not_allowed', 'admin'));
   $settings = Loader::getModule('CHSConfig')->getConfigSet('CHSShoutbox');
   CHSFunctions::printHead('CHS &ndash; Shoutbox: ' . $this->chsLanguage->getString('title', 'admin'), 'Shoutbox, CHS, ' . $this->chsLanguage->getString('title', 'admin') . ', Chrissyx', $this->chsLanguage->getString('descr', 'admin'), $this->chsLanguage->getString('charset', 'common'), $this->chsLanguage->getLangCode());
   switch(isset($_GET['page']) ? $_GET['page'] : '')
   {
    case 'smilies':
    if(empty($settings['loc_smilies']))
    {
     echo(CHSFunctions::getMsgBox($this->chsLanguage->getString('smilies_deactivated', 'smilies')));
     break;
    }
    elseif(basename($settings['loc_smilies']) == 'smilies.var')
    {
     echo(CHSFunctions::getMsgBox($this->chsLanguage->getString('smilies_via_tbb1', 'smilies')));
     break;
    }
    else
     $smilies = array_map('trim', file($settings['loc_smilies']));
    if(isset($_POST['update']))
    {
     $msg = CHSFunctions::getMsgBox($this->chsLanguage->getString('fill_out_all', 'common'), 'red');
     list($_POST['synonym'], $_POST['smiley']) = CHSFunctions::stripEscape($_POST['synonym'], $_POST['smiley']);
     if(empty($_POST['synonym']))
      $_POST['synonym'] .= CHSFunctions::$redBorder;
     elseif(empty($_POST['address']) && empty($_FILES['uploadpic']['name']))
     {
      $_POST['address'] .= CHSFunctions::$redBorder;
      $_FILES['uploadpic']['name'] .= CHSFunctions::$redBorder;
     }
     elseif(!empty($_FILES['uploadpic']['name']) && !CHSFunctions::isValidPicExt($_FILES['uploadpic']['name']))
      $_FILES['uploadpic']['name'] .= CHSFunctions::$redBorder;
     else
     {
      unset($msg);
      switch($_FILES['uploadpic']['error'])
      {
       case 0: //With upload
       if(move_uploaded_file($_FILES['uploadpic']['tmp_name'], $settings['folder_of_smilies'] . $_FILES['uploadpic']['name']))
       {
        chmod($settings['folder_of_smilies'] . $_FILES['uploadpic']['name'], 0775);
        $_POST['address'] = $_FILES['uploadpic']['name'];
       }
       else
       {
        echo(CHSFunctions::getMsgBox($this->chsLanguage->getString('pic_process_failed', 'smilies'), 'red'));
        break;
       }

       case 4: //No upload
       if(!empty($_POST['smiley']) && !empty($_POST['synonym'])) //Existing smiley
       {
        $value = explode("\t", $smilies[$key = CHSFunctions::unifyElement($smilies, $_POST['smiley'], 1, 1)]);
        if(isset($_POST['delete'])) //Delete smiley
        {
         if(file_exists($settings['folder_of_smilies'] . $value[2]))
          unlink($settings['folder_of_smilies'] . $value[2]);
         unset($smilies[$key]);
        }
        else //Edit smiley
        {
         if(($_POST['address'] != $value[2]) && !empty($value[2]) && file_exists($settings['folder_of_smilies'] . $value[2]))
          unlink($settings['folder_of_smilies'] . $value[2]);
         $smilies[$key] = $value[0] . "\t" . $_POST['synonym'] . "\t" . $_POST['address'];
        }
        file_put_contents($settings['loc_smilies'], implode("\n", $smilies));
        unset($_POST['synonym'], $_POST['address']);
        echo(CHSFunctions::getMsgBox($this->chsLanguage->getString('smiley_edited', 'smilies'), 'green'));
       }
       elseif(!empty($_POST['synonym']) && CHSFunctions::unifyElement($smilies, $_POST['synonym'], 1, 1) === false) //New smiley
       {
        $smilies[] = $smilies[0]++ . "\t" . $_POST['synonym'] . "\t" . $_POST['address'];
        file_put_contents($settings['loc_smilies'], implode("\n", $smilies));
        unset($_POST['synonym'], $_POST['address']);
        echo(CHSFunctions::getMsgBox($this->chsLanguage->getString('smiley_added', 'smilies'), 'green'));
       }
       else
        echo(CHSFunctions::getMsgBox(sprintf($this->chsLanguage->getString('smiley_exist', 'smilies'), $_POST['synonym']), 'yellow'));
       break;

       case 3:
       echo(CHSFunctions::getMsgBox($this->chsLanguage->getString('pic_partial_failed', 'smilies'), 'red'));
       $_FILES['uploadpic']['name'] .= CHSFunctions::$redBorder;
       break;

       case 2:
       case 1:
       echo(CHSFunctions::getMsgBox($this->chsLanguage->getString('pic_bigsize_failed', 'smilies'), 'red'));
       $_FILES['uploadpic']['name'] .= CHSFunctions::$redBorder;
       break;

       default:
       echo(CHSFunctions::getMsgBox($this->chsLanguage->getString('pic_unknown_failed', 'smilies'), 'red'));
       break;
      }
     }
     if(isset($msg))
      echo($msg);
    }
    array_shift($smilies); //Remove last smiley ID
    echo("\n" . '  <script type="text/javascript">' . "\n");
    $temp = '  var smilies = new Array(';
    foreach($smilies as $key => $value)
    {
     $value = explode("\t", $value);
     $temp .= 'new Array(\'' . strtr($value[1], CHSFunctions::getHTMLJSTransTable()) . '\', \'' . $value[2] . '\'), ';
    }
    echo($temp . "'Windows 98SE rulez');\n");
?>

  function fillForm(key)
  {
   document.getElementById('synonym').value = smilies[key][0];
   document.getElementById('address').value = smilies[key][1];
   document.getElementById('delete').disabled = false;
   document.getElementById('smiley').value = smilies[key][0];
  };
  </script>

  <h3>CHS &ndash; Shoutbox: <?=$this->chsLanguage->getString('title', 'smilies')?></h3>
  <p><?=sprintf($this->chsLanguage->getString('intro', 'smilies'), '"' . $_SERVER['PHP_SELF'] .'?module=CHSShoutboxAdmin&amp;action=admin"')?></p>
  <form action="<?=$_SERVER['PHP_SELF']?>?module=CHSShoutboxAdmin&amp;page=smilies" method="post" enctype="multipart/form-data">
  <table style="float:left;">
   <tr><td><?=$this->chsLanguage->getString('synoym', 'smilies')?></td><td><input type="text" name="synonym" id="synonym" value="<?=isset($_POST['synonym']) ? $_POST['synonym'] : ''?>" size="45" /></td></tr>
   <tr><td><?=$this->chsLanguage->getString('address', 'smilies')?></td><td><input type="text" name="address" id="address" value="<?=isset($_POST['address']) ? $_POST['address'] : ''?>" size="45" /></td></tr>
   <tr><td colspan="2"><?=$this->chsLanguage->getString('hint_add', 'smilies')?></td></tr>
   <tr><td><?=$this->chsLanguage->getString('upload', 'smilies')?></td><td><input type="file" name="uploadpic" value="<?=isset($_FILES['uploadpic']['name']) ? $_FILES['uploadpic']['name'] : ''?>" size="25" /></td></tr>
   <tr><td><?=$this->chsLanguage->getString('delete', 'smilies')?></td><td><span style="background-color:#FF0000;"><input type="checkbox" name="delete" id="delete" disabled="disabled" /></span></td></tr>
  </table>
  <div style="border:1px solid #000000; margin-left:10px; padding:5px; float:left;">
   <?=$this->chsLanguage->getString('edit_smiley', 'smilies')?><br />
<?php
for($i=0; $i<count($smilies);)
{
 $value = explode("\t", $smilies[$i]);
 echo('   <img src="' . (strpos($value[2], '/') === false ? $settings['folder_of_smilies'] : '') . $value[2] . '" alt="' . $value[1] . '" style="cursor:pointer;" onclick="fillForm(' . $i++ . ');" />');
 if(($i % $settings['smilies_per_row']) == 0)
  echo("<br />\n");
}
?>  </div>
  <br style="clear:both;" />
  <?=CHSFunctions::getFont(2) . $this->chsLanguage->getString('hint_delete', 'smilies')?></span><br /><br />
  <input type="submit" value="<?=$this->chsLanguage->getString('update_now', 'admin')?>" /> <input type="reset" onmouseup="document.getElementById('delete').disabled=true; document.getElementById('smiley').value='';" />
  <input type="hidden" name="action" value="admin" />
  <input type="hidden" name="update" value="true" />
  <input type="hidden" name="smiley" id="smiley" />
  </form>

<?php
    break;

    default:
    if(isset($_POST['update']))
    {
     $msg = CHSFunctions::getMsgBox($this->chsLanguage->getString('fill_out_all', 'common'), 'red');
     if(!is_numeric($_POST['shoutmax']))
      $settings['amount_of_shouts'] .= CHSFunctions::$redBorder;
     elseif(!empty($_POST['shoutarchivmax']) && !is_numeric($_POST['shoutarchivmax']))
      $settings['shouts_in_archive'] .= CHSFunctions::$redBorder;
     elseif(empty($_POST['shoutboxdat']))
      $settings['loc_shoutbox'] .= CHSFunctions::$redBorder;
     elseif(empty($_POST['shoutarchivdat']))
      $settings['loc_archive'] .= CHSFunctions::$redBorder;
     elseif(empty($_POST['redir']))
      $settings['loc_script'] .= CHSFunctions::$redBorder;
     elseif(!empty($_POST['email']) && !CHSFunctions::isValidMail($_POST['email']))
      $settings['mail_addr'] .= CHSFunctions::$redBorder;
     elseif(!empty($_POST['shoutsmilies']) && basename($_POST['shoutsmilies']) != 'smilies.var' && empty($_POST['smileypics']))
      $settings['folder_of_smilies'] .= CHSFunctions::$redBorder;
     elseif(!is_numeric($_POST['smiliesmax']) && !empty($_POST['shoutsmilies']))
      $settings['amount_of_smilies'] .= CHSFunctions::$redBorder;
     elseif(!is_numeric($_POST['smiliesmaxrow']) && !empty($_POST['shoutsmilies']))
      $settings['smilies_per_row'] .= CHSFunctions::$redBorder;
     elseif($_POST['shoutpw'] == $_POST['shoutpw2'])
     {
      unset($msg);
      //New language
      if($_POST['lang'] != $settings['lang'] && !$this->chsLanguage->setLangCode($_POST['lang']))
       echo(CHSFunctions::getMsgBox($this->chsLanguage->getString('cant_set_lang', 'admin'), 'yellow'));
      //New shoutbox file
      if($_POST['shoutboxdat'] != $settings['loc_shoutbox'] && !rename($settings['loc_shoutbox'], $_POST['shoutboxdat']))
      {
       echo(CHSFunctions::getMsgBox(sprintf($this->chsLanguage->getString('cant_rename_file_x', 'admin'), basename($_POST['shoutboxdat'] = $settings['loc_shoutbox']), 'yellow')));
       $settings['loc_shoutbox'] .= '" style="border-color:#FFD700;';
      }
      //New shoutbox archive file
      if($_POST['shoutarchivdat'] != $settings['loc_archive'] && !rename($settings['loc_archive'], $_POST['shoutarchivdat']))
      {
       echo(CHSFunctions::getMsgBox(sprintf($this->chsLanguage->getString('cant_rename_file_x', 'admin'), basename($_POST['shoutarchivdat'] = $settings['loc_archive']), 'yellow')));
       $settings['loc_archive'] .= '" style="border-color:#FFD700;';
      }
      //New password
      if(!empty($_POST['shoutpw']))
      {
       $_SESSION['shoutpw'] = CHSFunctions::getHash($_POST['shoutpw']);
       unlink($this->curPassFile); //Delete old and create new pass file
       CHSFunctions::setPHPDataFile($this->curPassFile = Loader::getDataPath() . md5(time()) . 'CHSShoutbox.dat', $_SESSION['shoutpw']);
      }
      //Smilies with three possibilities: Own .dat file, TBB1 smilies.var or none - Each case can transits into another one
      if($_POST['shoutsmilies'] != $settings['loc_smilies'])
      {
       //New or update own .dat file
       if(!empty($_POST['shoutsmilies']) && basename($_POST['shoutsmilies']) != 'smilies.var')
       {
        //New .dat file
        if(empty($settings['loc_smilies']) || basename($settings['loc_smilies']) == 'smilies.var')
        {
         if(!file_exists(dirname($_POST['shoutsmilies'])))
          mkdir(dirname($_POST['shoutsmilies']), 0755, true);
         file_put_contents($_POST['shoutsmilies'], '0');
         if(!file_exists($_POST['smileypics']))
          mkdir($_POST['smileypics'], 0755, true);
        }
        //Update .dat file
        else
        {
         if(!rename($settings['loc_smilies'], $_POST['shoutsmilies']))
         {
          echo(CHSFunctions::getMsgBox(sprintf($this->chsLanguage->getString('cant_rename_file_x', 'admin'), basename($_POST['shoutsmilies'] = $settings['loc_smilies']), 'yellow')));
          $settings['loc_smilies'] .= '" style="border-color:#FFD700;';
         }
         if(!rename($settings['folder_of_smilies'], $_POST['smileypics']))
         {
          echo(CHSFunctions::getMsgBox(sprintf($this->chsLanguage->getString('cant_rename_folder_x', 'admin'), basename($_POST['smileypics'] = $settings['folder_of_smilies']), 'yellow')));
          $settings['folder_of_smilies'] .= '" style="border-color:#FFD700;';
         }
        }
       }
       //Remove .dat file
       elseif((empty($_POST['shoutsmilies']) || basename($_POST['shoutsmilies']) == 'smilies.var') && !empty($settings['loc_smilies']) && basename($settings['loc_smilies']) != 'smilies.var')
       {
        unlink($settings['loc_smilies']);
        if(!@rmdir($settings['folder_of_smilies']))
        {
         foreach(glob($settings['folder_of_smilies'] . '*.*') as $value)
          unlink($value);
         rmdir($settings['folder_of_smilies']);
        }
        $_POST['smileypics'] = ''; //Remove from settings
       }
      }
      //Save settings
      Loader::getModule('CHSConfig')->setConfigSet('CHSShoutbox', array('lang' => $_POST['lang'], 'amount_of_shouts' => $_POST['shoutmax'], 'shouts_in_archive' => $_POST['shoutarchivmax'], 'loc_shoutbox' => $_POST['shoutboxdat'], 'loc_archive' => $_POST['shoutarchivdat'], 'loc_script' => $_POST['redir'], 'captcha' => isset($_POST['captcha']), 'mail_addr' => $_POST['email'], 'br' => (isset($_POST['compa']) ? "\n" : "\r\n"), 'loc_smilies' => $_POST['shoutsmilies'], 'folder_of_smilies' => $_POST['smileypics'], 'amount_of_smilies' => $_POST['smiliesmax'], 'smilies_per_row' => $_POST['smiliesmaxrow']));
      $settings = Loader::getModule('CHSConfig')->getConfigSet('CHSShoutbox'); //Reload settings
      echo(CHSFunctions::getMsgBox($this->chsLanguage->getString('new_settings_saved', 'admin'), 'green'));
     }
     if(isset($msg))
      echo($msg);
    }
?>

  <h3>CHS &ndash; Shoutbox: <?=$this->chsLanguage->getString('title', 'admin')?></h3>
  <p><?=sprintf($this->chsLanguage->getString('intro', 'admin'), '"' . $_SERVER['PHP_SELF'] .'?module=CHSShoutboxAdmin&amp;action=admin&amp;page=smilies"')?></p>
  <form name="form" action="<?=$_SERVER['PHP_SELF']?>?module=CHSShoutboxAdmin" method="post">
  <table>
   <tr><td><?=$this->chsLanguage->getString('language', 'admin')?></td><td><select name="lang" size="1" style="width:265px;"><option value=""><?=$this->chsLanguage->getString('automatically', 'admin')?></option><?php
foreach($this->chsLanguage->getLangCodes() as $curCode)
 echo('<option value="' . $curCode . '"' . ($settings['lang'] == $curCode ? ' selected="selected"' : '') . '>' . $this->chsLanguage->getString($curCode, 'common') . '</option>');
?></select></td></tr>
   <tr><th colspan="2"><?=$this->chsLanguage->getString('general', 'admin')?></th></tr>
   <tr><td><?=$this->chsLanguage->getString('amount_of_shouts', 'admin')?></td><td><input type="text" name="shoutmax" value="<?=$settings['amount_of_shouts']?>" size="40" /></td></tr>
   <tr><td><?=$this->chsLanguage->getString('shouts_in_archive', 'admin')?></td><td><input type="text" name="shoutarchivmax" value="<?=$settings['shouts_in_archive']?>" size="40" /></td></tr>
   <tr><td><?=$this->chsLanguage->getString('loc_shoutbox', 'admin')?></td><td><input type="text" name="shoutboxdat" value="<?=$settings['loc_shoutbox']?>" size="40" /></td></tr>
   <tr><td><?=$this->chsLanguage->getString('loc_archive', 'admin')?></td><td><input type="text" name="shoutarchivdat" value="<?=$settings['loc_archive']?>" size="40" /></td></tr>
   <tr><td><?=$this->chsLanguage->getString('loc_script', 'admin')?></td><td><input type="text" name="redir" value="<?=$settings['loc_script']?>" size="40" /></td></tr>
   <tr><td><?=$this->chsLanguage->getString('captcha', 'admin')?></td><td><input type="checkbox" name="captcha"<?=$settings['captcha'] ? ' checked="checked"' : ''?> /></td></tr>
   <tr><th colspan="2"><?=$this->chsLanguage->getString('account', 'admin')?></th></tr>
   <tr><td><?=$this->chsLanguage->getString('mail_addr', 'admin')?></td><td><input type="text" name="email" value="<?=$settings['mail_addr']?>" size="40" /></td></tr>
   <tr><td><?=$this->chsLanguage->getString('mail_comp', 'admin')?></td><td><input type="checkbox" name="compa"<?=$settings['br'] == "\n" ? ' checked="checked"' : ''?> /></td></tr>
   <tr><td colspan="2"><?=CHSFunctions::getFont(1) . $this->chsLanguage->getString('password_hint', 'admin')?></span></td></tr>
   <tr><td><?=$this->chsLanguage->getString('password', 'admin')?></td><td><input type="password" name="shoutpw"<?php if(isset($_POST['update']) && $_POST['shoutpw'] != $_POST['shoutpw2']) echo(' style="border-color:#FF0000;"'); ?> size="40" /></td></tr>
   <tr><td><?=$this->chsLanguage->getString('retype_pass', 'admin')?></td><td><input type="password" name="shoutpw2"<?php if(isset($_POST['update']) && $_POST['shoutpw'] != $_POST['shoutpw2']) echo(' style="border-color:#FF0000;"'); ?> size="40" /></td></tr>
   <tr><th colspan="2"><?=$this->chsLanguage->getString('smilies', 'admin')?></th></tr>
   <tr><td><?=$this->chsLanguage->getString('loc_smilies', 'admin')?></td><td><input type="text" name="shoutsmilies" value="<?=$settings['loc_smilies']?>" size="40" /></td></tr>
   <tr><td><?=$this->chsLanguage->getString('folder_of_smilies', 'admin')?></td><td><input type="text" name="smileypics" value="<?=$settings['folder_of_smilies']?>" size="40" /></td></tr>
   <tr><td><?=$this->chsLanguage->getString('amount_of_smilies', 'admin')?></td><td><input type="text" name="smiliesmax" value="<?=$settings['amount_of_smilies']?>" size="40" /></td></tr>
   <tr><td><?=$this->chsLanguage->getString('smilies_per_row', 'admin')?></td><td><input type="text" name="smiliesmaxrow" value="<?=$settings['smilies_per_row']?>" size="40" /></td></tr>
  </table>
  <input type="submit" value="<?=$this->chsLanguage->getString('update_now', 'admin')?>" /> <input type="reset" /> <input type="button" value="<?=$this->chsLanguage->getString('title', 'logout')?>" onclick="document.location='<?=$_SERVER['PHP_SELF']?>?module=CHSShoutboxAdmin&amp;action=logout';" />
  <input type="hidden" name="action" value="admin" />
  <input type="hidden" name="update" value="true" />
  </form>

<?php
    break;
   }
   CHSFunctions::printTail('CHSShoutbox', 'common');
   break;

# Logout #
   case 'logout':
   session_unset(); //Kill off whole session to re-init the Core to avoid caching issues
   @header('Location: ' . $_SERVER['PHP_SELF'] . '?module=CHSShoutboxAdmin');
   exit($this->chsLanguage->getString('logged_out', 'logout') . ' <a href="' . $_SERVER['PHP_SELF'] . '?module=CHSShoutboxAdmin">' . $this->chsLanguage->getString('go_on', 'common') . '</a>');
   break;

# New password #
   case 'newpass':
   CHSFunctions::printHead('CHS &ndash; Shoutbox: ' . $this->chsLanguage->getString('title', 'newpass'), 'Shoutbox, CHS, ' . $this->chsLanguage->getString('title', 'newpass') . ', Chrissyx', $this->chsLanguage->getString('descr', 'newpass'), $this->chsLanguage->getString('charset', 'common'), $this->chsLanguage->getLangCode());
   $settings = Loader::getModule('CHSConfig')->getConfigSet('CHSShoutbox');
   if(empty($settings['mail_addr']))
    echo(CHSFunctions::getMsgBox($this->chsLanguage->getString('no_addr_given', 'newpass'), 'yellow'));
   else
   {
    for($i=0,$newPass=''; $i<10; $i++)
     $newPass .= chr(mt_rand(33, 126));
    CHSFunctions::setPHPDataFile(substr($this->curPassFile, 0, -4), array($this->curPassHash, CHSFunctions::getHash($newPass)));
    echo(mail($settings['mail_addr'], str_replace('www.', '', $_SERVER['SERVER_NAME']) . ' Shoutbox: ' . $this->chsLanguage->getString('title', 'newpass'), sprintf($this->chsLanguage->getString('mail_text', 'newpass'), $_SERVER['REMOTE_ADDR'], $_SERVER['SERVER_NAME'], $newPass), 'From: shoutbox@' . str_replace('www.', '', $_SERVER['SERVER_NAME']) . $settings['br'] . 'Reply-To: ' . $settings['mail_addr'] . $settings['br'] . 'X-Mailer: PHP/' . phpversion() . $settings['br'] . 'Content-Type: text/plain; charset=' . $this->chsLanguage->getString('charset', 'common')) ? CHSFunctions::getMsgBox($this->chsLanguage->getString('mail_sent', 'newpass'), 'green') : CHSFunctions::getMsgBox($this->chsLanguage->getString('mail_not_sent', 'newpass'), 'red'));
   }
   CHSFunctions::printTail('CHSShoutbox', 'common');
   break;

# Installation #
   case 'install':
   default:
   CHSFunctions::printHead('CHS &ndash; Shoutbox: ' . $this->chsLanguage->getString('title', 'install'), 'Shoutbox, CHS, ' . $this->chsLanguage->getString('title', 'install') . ', Chrissyx', $this->chsLanguage->getString('descr', 'install'), $this->chsLanguage->getString('charset', 'common'), $this->chsLanguage->getLangCode());
   if($this->action == 'install')
   {
    $msg = CHSFunctions::getMsgBox($this->chsLanguage->getString('fill_out_all', 'common'), 'red');
    if(!is_numeric($_POST['shoutmax']))
     $_POST['shoutmax'] .= CHSFunctions::$redBorder;
    elseif(!empty($_POST['shoutarchivmax']) && !is_numeric($_POST['shoutarchivmax']))
     $_POST['shoutarchivmax'] .= CHSFunctions::$redBorder;
    elseif(empty($_POST['shoutboxdat']))
     $_POST['shoutboxdat'] .= CHSFunctions::$redBorder;
    elseif(empty($_POST['shoutarchivdat']))
     $_POST['shoutarchivdat'] .= CHSFunctions::$redBorder;
    elseif(empty($_POST['redir']))
     $_POST['redir'] .= CHSFunctions::$redBorder;
    elseif(!empty($_POST['email']) && !CHSFunctions::isValidMail($_POST['email']))
     $_POST['email'] .= CHSFunctions::$redBorder;
    elseif(empty($_POST['shoutpw']) || $_POST['shoutpw'] != $_POST['shoutpw2'])
     $_POST['shoutpw'] = $_POST['shoutpw2'] = ' style="border-color:#FF0000;"';
    elseif(!empty($_POST['shoutsmilies']) && basename($_POST['shoutsmilies']) != 'smilies.var' && empty($_POST['smileypics']))
     $_POST['smileypics'] .= CHSFunctions::$redBorder;
    elseif(!is_numeric($_POST['smiliesmax']) && !empty($_POST['shoutsmilies']))
     $_POST['smiliesmax'] .= CHSFunctions::$redBorder;
    elseif(!is_numeric($_POST['smiliesmaxrow']) && !empty($_POST['shoutsmilies']))
     $_POST['smiliesmaxrow'] .= CHSFunctions::$redBorder;
    else
    {
     if(!file_exists(dirname($_POST['shoutboxdat'])))
      mkdir(dirname($_POST['shoutboxdat']), 0755, true);
     file_put_contents($_POST['shoutboxdat'], '');
     if(!file_exists(dirname($_POST['shoutarchivdat'])))
      mkdir(dirname($_POST['shoutarchivdat']), 0755, true);
     file_put_contents($_POST['shoutarchivdat'], '');
     if(!empty($_POST['shoutsmilies']) && basename($_POST['shoutsmilies']) != 'smilies.var')
     {
      if(!file_exists(dirname($_POST['shoutsmilies'])))
       mkdir(dirname($_POST['shoutsmilies']), 0755, true);
      file_put_contents($_POST['shoutsmilies'], '0');
      if(!file_exists($_POST['smileypics']))
       mkdir($_POST['smileypics'], 0755, true);
     }
     CHSFunctions::setPHPDataFile(Loader::getDataPath() . md5(time()) . 'CHSShoutbox.dat', CHSFunctions::getHash($_POST['shoutpw']));
     Loader::getModule('CHSConfig')->setConfigSet('CHSShoutbox', array('lang' => '', 'amount_of_shouts' => $_POST['shoutmax'], 'shouts_in_archive' => $_POST['shoutarchivmax'], 'loc_shoutbox' => $_POST['shoutboxdat'], 'loc_archive' => $_POST['shoutarchivdat'], 'loc_script' => $_POST['redir'], 'captcha' => isset($_POST['captcha']), 'mail_addr' => $_POST['email'], 'br' => (isset($_POST['compa']) ? "\n" : "\r\n"), 'loc_smilies' => $_POST['shoutsmilies'], 'folder_of_smilies' => $_POST['smileypics'], 'amount_of_smilies' => $_POST['smiliesmax'], 'smilies_per_row' => $_POST['smiliesmaxrow']));
     file_put_contents(Loader::getConfigPath() . 'CHSShoutbox.ini', 'notifyOnLoad = On'); //Enable shoutbox for onLoad notification
     echo(CHSFunctions::getMsgBox($this->chsLanguage->getString('install_finished', 'install'), 'green'));
?>

  <p><?=$this->chsLanguage->getString('note1', 'install')?></p>
  <p><code>&lt;!-- CHS - Shoutbox --&gt;&lt;?php Loader::execute('CHSShoutbox'); ?&gt;&lt;!-- /CHS - Shoutbox --&gt;</code></p>
  <p><?=$this->chsLanguage->getString('note2', 'install')?></p>
  <p><code>&lt;?php include('chscore/CHSCore.php'); ?&gt;</code></p>
  <p><?=sprintf($this->chsLanguage->getString('note3', 'install'), '<a href="http://www.chrissyx-forum.de.vu/" target="_blank">http://www.chrissyx-forum.de.vu/</a>')?></p>
  <p><a href="http://<?=$_SERVER['SERVER_NAME']?>/"><?=$this->chsLanguage->getString('goto1', 'install')?></a> &ndash; <a href="<?=$_SERVER['PHP_SELF']?>?module=CHSShoutboxAdmin"><?=$this->chsLanguage->getString('goto2', 'install')?></a></p>

<?php
     CHSFunctions::printTail('CHSShoutbox', 'common');
     exit(session_unset()); //Kill off whole session to re-init the Core to avoid issues with cached onLoad infos
	}
   }
   if(isset($msg))
    echo $msg;
   if(phpversion() < '5.1')
    echo(CHSFunctions::getMsgBox(sprintf($this->chsLanguage->getString('warning_php_version', 'install'), PHP_VERSION), 'red'));
?>

  <script type="text/javascript">
  function help(data)
  {
   document.getElementById('help').firstChild.nodeValue = data;
  };
  </script>

  <h3>CHS &ndash; Shoutbox: <?=$this->chsLanguage->getString('title', 'install')?></h3>
  <p><?=$this->chsLanguage->getString('intro', 'install')?></p>
  <form action="<?=$_SERVER['PHP_SELF']?>?module=CHSShoutboxAdmin" method="post">
  <table onmouseout="help('<?=$this->chsLanguage->getString('help', 'install')?>');">
   <tr><td colspan="2"></td><td rowspan="18" style="background-color:yellow; width:200px;"><div class="center" id="help"><?=$this->chsLanguage->getString('help', 'install')?></div></td></tr>
   <tr><th colspan="2"><?=$this->chsLanguage->getString('general', 'admin')?></th></tr>
   <tr onmouseover="help('<?=$this->chsLanguage->getString('help1', 'install')?>');"><td><?=$this->chsLanguage->getString('amount_of_shouts', 'admin')?></td><td><input type="text" name="shoutmax" value="<?=isset($_POST['shoutmax']) ? $_POST['shoutmax'] : '5'?>" size="40" /></td></tr>
   <tr onmouseover="help('<?=$this->chsLanguage->getString('help2', 'install')?>');"><td><?=$this->chsLanguage->getString('shouts_in_archive', 'admin')?></td><td><input type="text" name="shoutarchivmax" value="<?=isset($_POST['shoutarchivmax']) ? $_POST['shoutarchivmax'] : ''?>" size="40" onfocus="this.value = this.value == '' ? '50' : this.value;" /></td></tr>
   <tr onmouseover="help('<?=$this->chsLanguage->getString('help3', 'install')?>');"><td><?=$this->chsLanguage->getString('loc_shoutbox', 'admin')?></td><td><input type="text" name="shoutboxdat" value="<?=isset($_POST['shoutboxdat']) ? $_POST['shoutboxdat'] : Loader::getDataPath() . 'shoutbox.dat'?>" size="40" /></td></tr>
   <tr onmouseover="help('<?=$this->chsLanguage->getString('help4', 'install')?>');"><td><?=$this->chsLanguage->getString('loc_archive', 'admin')?></td><td><input type="text" name="shoutarchivdat" value="<?=isset($_POST['shoutarchivdat']) ? $_POST['shoutarchivdat'] : Loader::getDataPath() . 'archiv.dat'?>" size="40" /></td></tr>
   <tr onmouseover="help('<?=sprintf($this->chsLanguage->getString('help5', 'install'), $_SERVER['SERVER_NAME'])?>');"><td><?=$this->chsLanguage->getString('loc_script', 'admin')?></td><td><input type="text" name="redir" value="<?=isset($_POST['redir']) ? $_POST['redir'] : ''?>" size="40" onfocus="this.value = this.value == '' ? 'http://' : this.value;" /></td></tr>
   <tr onmouseover="help('<?=$this->chsLanguage->getString('help6', 'install')?>');"><td><?=$this->chsLanguage->getString('captcha', 'admin')?></td><td><input type="checkbox" name="captcha"<?=isset($_POST['captcha']) ? ' checked="checked"' : ''?> /></td></tr>
   <tr><th colspan="2"><?=$this->chsLanguage->getString('account', 'admin')?></th></tr>
   <tr onmouseover="help('<?=$this->chsLanguage->getString('help7', 'install')?>');"><td><?=$this->chsLanguage->getString('mail_addr', 'admin')?></td><td><input type="text" name="email" value="<?=isset($_POST['email']) ? $_POST['email'] : ''?>" size="40" /></td></tr>
   <tr onmouseover="help('<?=$this->chsLanguage->getString('help8', 'install')?>');"><td><?=$this->chsLanguage->getString('mail_comp', 'admin')?></td><td><input type="checkbox" name="compa"<?=isset($_POST['compa']) ? ' checked="checked"' : ''?> /></td></tr>
   <tr onmouseover="help('<?=$this->chsLanguage->getString('help9', 'install')?>');"><td><?=$this->chsLanguage->getString('password', 'admin')?></td><td><input type="password" name="shoutpw" size="40"<?=isset($_POST['shoutpw']) ? $_POST['shoutpw'] : ''?> /></td></tr>
   <tr onmouseover="help('<?=$this->chsLanguage->getString('help10', 'install')?>');"><td><?=$this->chsLanguage->getString('retype_pass', 'admin')?></td><td><input type="password" name="shoutpw2" size="40"<?=isset($_POST['shoutpw2']) ? $_POST['shoutpw2'] : ''?> /></td></tr>
   <tr><th colspan="2"><?=$this->chsLanguage->getString('smilies', 'admin')?></th></tr>
   <tr onmouseover="help('<?=$this->chsLanguage->getString('help11', 'install')?>');"><td><?=$this->chsLanguage->getString('loc_smilies', 'admin')?></td><td><input type="text" name="shoutsmilies" value="<?=isset($_POST['shoutsmilies']) ? $_POST['shoutsmilies'] : ''?>" size="40" onfocus="this.value = this.value == '' ? (confirm('<?=$this->chsLanguage->getString('use_tbb_smilies', 'install')?>') ? 'forum/vars/smilies.var' : '<?=Loader::getDataPath()?>smilies.dat') : this.value;" /></td></tr>
   <tr onmouseover="help('<?=$this->chsLanguage->getString('help12', 'install')?>');"><td><?=$this->chsLanguage->getString('folder_of_smilies', 'admin')?></td><td><input type="text" name="smileypics" value="<?=isset($_POST['smileypics']) ? $_POST['smileypics'] : ''?>" size="40" onfocus="this.value = this.value == '' ? '<?=Loader::getImagesPath()?>CHSShoutbox/' : this.value;" /></td></tr>
   <tr onmouseover="help('<?=$this->chsLanguage->getString('help13', 'install')?>');"><td><?=$this->chsLanguage->getString('amount_of_smilies', 'admin')?></td><td><input type="text" name="smiliesmax" value="<?=isset($_POST['smiliesmax']) ? $_POST['smiliesmax'] : '22'?>" size="40" /></td></tr>
   <tr onmouseover="help('<?=$this->chsLanguage->getString('help14', 'install')?>');"><td><?=$this->chsLanguage->getString('smilies_per_row', 'admin')?></td><td><input type="text" name="smiliesmaxrow" value="<?=isset($_POST['smiliesmaxrow']) ? $_POST['smiliesmaxrow'] : '11'?>" size="40" /></td></tr>
  </table>
  <input type="submit" value="<?=$this->chsLanguage->getString('install_now', 'install')?>" onmouseover="help('<?=$this->chsLanguage->getString('help15', 'install')?>');" /> <input type="reset" onmouseover="help('<?=$this->chsLanguage->getString('help16', 'install')?>');" />
  <input type="hidden" name="action" value="install" />
  </form>

<?php
   CHSFunctions::printTail('CHSShoutbox', 'common');
   break;
  }
 }

 /**
  * Detects valid user action and prepares password hashes.
  *
  * @see CHSCore::onLoad()
  */
 public function onLoad()
 {
  if(isset($_GET['module']) && $_GET['module'] == get_class())
  {
   Loader::execute('CHSFunctions');
   $this->action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
   //Update reference to lang module
   $this->chsLanguage = Loader::getModule('CHSLanguage');
   if(!$this->chsLanguage->setModule('CHSShoutbox')) //Set shortcut
    trigger_error(__METHOD__ . '(): Cannot set module name as shortcut', E_USER_WARNING);
   if(Loader::getModule('CHSConfig')->hasConfigSet('CHSShoutbox'))
   {
    if(($code = Loader::getModule('CHSConfig')->getConfigValue('CHSShoutbox', 'lang')) != '')
     $this->chsLanguage->setLangCode($code);
    if(!in_array($this->action, array('login', 'logout', 'admin', 'newpass')))
     $this->action = 'login';
    $this->curPassHash = @current($passHashes = CHSFunctions::getPHPDataFile(substr($this->curPassFile = current(glob(Loader::getDataPath() . '*CHSShoutbox.dat.php')), 0, -4))) or exit($this->chsLanguage->getString('error_no_user', 'admin'));
    $this->newPassHash = next($passHashes);
   }
   exit($this->execute());
  }
 }
}
?>