<?php
/**
 * Shoutbox zum Anzeigen und Verwalten der Shouts. Verarbeitet auch Login und Passwörter.
 * 
 * @author Chrissyx
 * @copyright (c) 2006 - 2009 by Chrissyx
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ Creative Commons 3.0 by-nc-sa
 * @package CHS_Shoutbox
 * @version 0.9.11
 */
/**
 * Generiert den XHTML Head für jede interne Seite der Shoutbox und sendet den passenden Content-Type, wenn der Browser XML unterstützt.
 * 
 * @author Chrissyx
 * @copyright Chrissyx
 * @param string Der Titel des Dokuments
 * @param string Metatag für Schlüsselwörter
 * @param string Metatag für Beschreibung
 * @param string Weitere optionale XHTML Tags im Head
 */
function headBox($title, $keywords, $description, $sonstiges=null)
{
 if(stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml')) header('Content-Type: application/xhtml+xml');
 echo('<?xml version="1.0" encoding="ISO-8859-1" standalone="no" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de">
 <head>
  <title>' . $title . '</title>
  <meta name="author" content="Chrissyx" />
  <meta name="copyright" content="Chrissyx" />
  <meta name="keywords" content="' . $keywords . '" />
  <meta name="description" content="' . $description . '" />
  <meta name="robots" content="all" />
  <meta name="revisit-after" content="7 days" />
  <meta name="generator" content="Notepad 4.10.1998" />
  <meta http-equiv="content-language" content="de" />
  <meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
  <meta http-equiv="content-style-type" content="text/css" />
  <meta http-equiv="content-script-type" content="text/javascript" />
');
  if($sonstiges) echo("  $sonstiges\n");
  echo(' </head>
 <body>
');
}

//Caching
if(file_exists('shoutbox/settings.php') && (filemtime('shoutbox/settings.php') > filemtime('shoutbox/settings.dat'))) include('shoutbox/settings.php');
else
{
 //Config: Shoutbox, Anzahl Archiv, Archiv, Passwort, Anzahl, TBB Smilies, Smilies Anzahl, Smilies Anzahl Reihe, Redir nach Login
 list($shoutboxdat, $shoutarchivmax, $shoutarchivdat, $shoutpwdat, $shoutmax, $smilies, $smiliesmax, $smiliesmaxrow, $redir) = @array_map('trim', file('shoutbox/settings.dat')) or die('<b>ERROR:</b> Keine Einstellungen gefunden!');
 $forum = implode('/', array_slice(explode('/', $smilies), 0, -2));
 $temp = fopen('shoutbox/settings.php', 'w');
 fwrite($temp, "<?php\n //Auto-generated config!\n \$shoutboxdat = '$shoutboxdat';\n \$shoutarchivmax = " . ($shoutarchivmax ? $shoutarchivmax : "''") . ";\n \$shoutarchivdat = '$shoutarchivdat';\n \$shoutpwdat = '$shoutpwdat';\n \$shoutmax = $shoutmax;\n \$smilies = array_map('trim', " . (file_exists($smilies) ? "file('$smilies')" : 'array()') . ");\n \$smiliesmax = " . ($smiliesmax ? $smiliesmax : "''") . ";\n \$smiliesmaxrow = " . ($smiliesmaxrow ? $smiliesmaxrow : "''") . ";\n \$redir = '$redir';\n \$forum = '$forum';\n?>");
 fclose($temp);
 $smilies = array_map('trim', file_exists($smilies) ? file($smilies) : array());
}

//$action laden
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
session_start();

//Mehr Smilies
if($action == 'smilies')
{
 headBox('CHS - Shoutbox: Mehr Smilies', 'Shoutbox, CHS, Mehr Smilies, Chrissyx', 'Mehr Smilies der Shoutbox von CHS');
 $size = count($smilies);
 for($i=0; $i<$size; $i++)
 {
  $smilie = explode("\t", $smilies[$i]);
  if(($i % $smiliesmaxrow) == 0) echo("  <br />\n");
  echo("  <a href=\"javascript:opener.document.getElementById('shoutboxform').shoutbox.value += ' " . $smilie[1] . " '; opener.document.getElementById('shoutboxform').shoutbox.focus();\"><img src=\"$forum/" . $smilie[2] . '" style="border:none" alt="' . $smilie[1] . "\" /></a>\n");
 }
 die(" </body>\n</html>");
}

//Admin Login
elseif($action == 'admin')
{
 $pw = @file_get_contents($shoutpwdat) or die('<b>ERROR:</b> Passwort noch nicht angelegt!');
 $_SESSION['dispall'] = false;
 if(md5($_POST['shoutpw']) == $pw)
 {
  $_SESSION['shoutpw'] = md5($_POST['shoutpw']);
  unset($_POST['shoutpw']);
  if($_POST['edit'] == 'box') $redir = 'shoutbox/index.php';
  else $_SESSION['dispall'] = true;
  if($redir)
  {
   @header('Location: ' . $redir);
   die('Eingeloggt! <a href="' . $redir . '">Zurück zur Seite...</a>');
  }
 }
 else
 {
  headBox('CHS - Shoutbox: Login', 'Shoutbox, CHS, Login, Chrissyx', 'Login der Shoutbox von CHS', '<link rel="stylesheet" media="all" href="shoutbox/style.css" />');
  ?>
  <h4>CHS - Shoutbox: Login</h4>
  <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
  Bitte Passwort angeben: <input type="password" name="shoutpw" <?php if($_POST['shoutpw']) echo('style="border-color:#FF0000;" /><br />
  <span style="color:#FF0000; font-weight:bold;">&raquo; Falsches Passwort!</span><br '); ?>/><br />
  <input type="radio" name="edit" value="shouts" checked="checked" />Shouts verwalten<br />
  <input type="radio" name="edit" value="box" />Box verwalten<br />
  <input type="submit" value="Einloggen" />
  <input type="hidden" name="action" value="admin" />
  </form>
 <?php
  die("</body>\n</html>");
 }
}

//Admin Logout
elseif($action == 'shoutout') unset($_SESSION['shoutpw'], $_SESSION['dispall']);

//Archiv aufrufen
if($action == 'archiv')
{
 headBox('CHS - Shoutbox: Archiv', 'Shoutbox, CHS, Archiv, Chrissyx', 'Archiv der Shoutbox von CHS');
 if(!$temp = file($shoutarchivdat)) die("  Archiv ist leer!\n </body>\n</html>");
 if($_SESSION['dispall']) foreach($temp as $key => $value)
                          {
                           $value = explode("\t", $value);
                           echo('  <strong>' . $value[3] . '</strong> am <strong>' . $value[1] . '</strong> um <strong>' . $value[2] . "</strong>:<br />\n  " . $value[4] . "<br />\n  <div style=\"white-space:nowrap;\">IP: <strong>" . $value[0] . '</strong> - [ <a href="' . $_SERVER['PHP_SELF'] . "?action=deleteold&amp;id=$key\">Delete</a> ]</div>\n  <hr noshade=\"noshade\" />\n");
                          }
 else if($shoutarchivmax)
      {
       $size = count($temp);
       $_GET['page'] = ($_GET['page'] < 0) ? 0 : (($_GET['page']*$shoutarchivmax >= $size) ? $_GET['page']-1 : $_GET['page']);
       $start = $_GET['page']*$shoutarchivmax;
       $end = $start+$shoutarchivmax;
       echo('  <div style="text-align:center;"><strong><a href="' . $_SERVER['PHP_SELF'] . '?action=archiv&amp;page=' . ($_GET['page']-1) . '">&laquo; Zurück</a> - Seite ' . ($_GET['page']+1) . ' - <a href="' . $_SERVER['PHP_SELF'] . '?action=archiv&amp;page=' . ($_GET['page']+1) . "\">Vor &raquo;</a></strong><br />\n  <strong>Zeige " . ($start+1) . ' bis ' . (($end > $size) ? $size : $end) . " von $size Shouts gesamt:</strong></div><br />\n");
       for($i=$start; $i<$end; $i++)
       {
        if(!$temp[$i]) break;
        $value = explode("\t", $temp[$i]);
        echo('  <strong>' . $value[3] . '</strong> am <strong>' . $value[1] . '</strong> um <strong>' . $value[2] . "</strong>:<br />\n  " . $value[4] . "<br />\n  <hr noshade=\"noshade\" />\n");
       }
       echo('  <div style="text-align:center;"><strong><a href="' . $_SERVER['PHP_SELF'] . '?action=archiv&amp;page=' . ($_GET['page']-1) . '">&laquo; Zurück</a> - Seite ' . ($_GET['page']+1) . ' - <a href="' . $_SERVER['PHP_SELF'] . '?action=archiv&amp;page=' . ($_GET['page']+1) . "\">Vor &raquo;</a></strong></div>\n");
      }
      else foreach($temp as $value)
           {
            $value = explode("\t", $value);
            echo('  <strong>' . $value[3] . '</strong> am <strong>' . $value[1] . '</strong> um <strong>' . $value[2] . "</strong>:<br />\n  " . $value[4] . "<br />\n  <hr noshade=\"noshade\" />\n");
           }
 echo(" </body>\n</html>");
}

//Aus Archiv löschen
elseif($action == 'deleteold')
{
 $pw = @file_get_contents($shoutpwdat) or die('<b>ERROR:</b> Passwort noch nicht angelegt!');
 if(($_SESSION['shoutpw'] == $pw) && $_SESSION['dispall'])
 {
  $towrite = file($shoutarchivdat);
  if(!$towrite[$_GET['id']]) die('<b>ERROR:</b> Shout nicht gefunden!');
  array_splice($towrite, $_GET['id'], 1);
  $temp = fopen($shoutarchivdat, 'w');
  fwrite($temp, implode('', $towrite));
  fclose($temp);
  @header('Location: ' . $_SERVER['PHP_SELF'] . '?action=archiv');
  die('Eintrag gelöscht! <a href="' . $_SERVER['PHP_SELF'] . '?action=archiv">Zurück zum Archiv...</a>');
 }
 else echo('<b>ERROR:</b> Keine Adminrechte!');
}

//Formular zeigen
else
{
 if($_POST['name']) $_SESSION['shoutName'] = $_POST['name'];
 ?>

<script type="text/javascript">
/**
 * Activates the submit button, depending on the stated name.
 * 
 * @author Chrissyx
 * @copyright (c) 2001 - 2009 by Chrissyx
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ Creative Commons 3.0 by-nc-sa
 * @link http://www.chrissyx.de(.vu)/
 * @since 0.9
 * @version 1.0
 */
function canShout()
{
 (sbform = document.getElementById('shoutboxform')).shout.disabled = sbform.name.value.length != 0 ? false : true;
}

/**
 * Adds a smilie-string to the textbox.
 * 
 * @author Chrissyx
 * @copyright (c) 2001 - 2009 by Chrissyx
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ Creative Commons 3.0 by-nc-sa
 * @link http://www.chrissyx.de(.vu)/
 * @since 0.9
 * @version 1.0
 */
function setShoutSmilie(smilie)
{
 (sbbox = document.getElementById('shoutboxform').shoutbox).value += smilie;
 sbbox.focus();
}
</script>

<a id="shoutbox" name="shoutbox"></a>
<form id="shoutboxform" name="shoutboxform" action="<?=$_SERVER['PHP_SELF']?>#shoutbox" method="post" onmouseover="canShout();">
<div style="white-space:nowrap;">Nick: <input type="text" name="name" size="17" value="<?=$_SESSION['shoutName']?>" onchange="canShout();" /><br />
<textarea name="shoutbox" rows="3" cols="18"></textarea><?php
//Smilies
if($smilies)
{
 $size = count($smilies);
 for($i=0; $i<$size; $i++)
 {
  $smilie = explode("\t", $smilies[$i]);
  $key[] = $smilie[1];
  $value[] = '<img src="' . $forum . '/' . $smilie[2] . '" alt="' . $smilie[1] . '" style="border:none;" />';
  if($i<$smiliesmax)
  {
   if(($i % $smiliesmaxrow) == 0) echo("<br />\n");
   echo("<a href=\"javascript:setShoutSmilie(' " . $smilie[1] . "');\">" . $value[$i] . '</a>');
  }
 }
 $smilies = array_combine($key, $value);
}
?><br />
<input type="submit" name="shout" value="Shout!" style="width:53px;" readonly="readonly" /> <input type="reset" value="Reset" style="width:53px;" /> <input type="button" value="Archiv" style="width:53px;" onclick="window.open('shoutbox.php?action=archiv&amp;page=0', '_blank', 'width=400, resizable, scrollbars, status');" /><br />
<input type="button" value="Reload" onclick="window.location='<?=$_SERVER['PHP_SELF']?>#shoutbox'; location.reload();" style="width:<?=($smilies) ? "63px;\" /> <input type=\"button\" value=\"Mehr Smilies\" style=\"width:100px;\" onclick=\"window.open('shoutbox.php?action=smilies', '_blank', 'width=250, resizable, scrollbars, status')" : '167px'?>;" />
</div>
<input type="hidden" name="action" value="shout" />
<?php if($_SESSION['shoutpw'] && $_SESSION['dispall']) echo('<a href="' . $_SERVER['PHP_SELF'] . '?action=shoutout">Logout</a>'); ?>
</form>

<?php
//Shouten
 if($action == 'shout')
 {
  $towrite = file($shoutboxdat);                                                                                                                                
  array_unshift($towrite, $_SERVER['REMOTE_ADDR'] . "\t" . date('d.m.Y') . "\t" . date('H:i:s') . "\t" . (($_POST['name']) ? htmlentities(stripslashes($_POST['name']), ENT_QUOTES) : $_SERVER['REMOTE_ADDR']) . "\t" . ereg_replace("(\r)(\n)", '' , nl2br(htmlentities(stripslashes($_POST['shoutbox']), ENT_QUOTES))) . "\n");
  while(count($towrite) > $shoutmax)
  {
   $archiv = file($shoutarchivdat);
   array_unshift($archiv, $towrite[count($towrite)-1]);
   array_pop($towrite);
   $temp = fopen($shoutarchivdat, 'w');
   fwrite($temp, implode('', $archiv));
   fclose($temp);
  }
  $temp = fopen($shoutboxdat, 'w');
  fwrite($temp, implode('', $towrite));
  fclose($temp);
  $_SESSION['shoutName'] = $_POST['name'];
 }

//Shouts löschen
 elseif($action == 'deleteshout')
 {
  $pw = @file_get_contents($shoutpwdat) or die('<b>ERROR:</b> Passwort noch nicht angelegt!');
  if(($_SESSION['shoutpw'] == $pw) && $_SESSION['dispall'])
  {
   $towrite = file($shoutboxdat);
   if(!$towrite[$_GET['id']]) die('<b>ERROR:</b> Shout nicht gefunden!');
   array_splice($towrite, $_GET['id'], 1);
   $archiv = file($shoutarchivdat);
   $towrite[] = $archiv[0];
   array_shift($archiv);
   $temp = fopen($shoutarchivdat, 'w');
   fwrite($temp, implode('', $archiv));
   fclose($temp);
   $temp = fopen($shoutboxdat, 'w');
   fwrite($temp, implode('', $towrite));
   fclose($temp);
   echo("Shout gelöscht!<br />\n");
  }
  else echo("<b>ERROR:</b> Keine Adminrechte!<br />\n");
 }

//Shouts zeigen (Achtung: 1337! Verstehen auf eigene Gefahr!)
 if(!$temp = file($shoutboxdat)) echo('Keine Shouts gefunden!');
 else foreach($temp as $key => $value)
      {
       $value = explode("\t", $value);
       $value[4] = preg_replace_callback("/([^ ^>]+?:\/\/|www\.)[^ ^<^\.]+(\.[^ ^<^\.]+)+/si", create_function('$arr', "return (\$arr[2]) ? '<a href=\"' . ((\$arr[1] == 'www.') ? 'http://' : '') . \$arr[0] . '\" target=\"blank\">' . ((strlen(\$arr[0]) > 25) ? substr(\$arr[0], 0, 15) . '...' . substr(\$arr[0], -10) : \$arr[0]) . '</a>' : \$arr[0];"), $value[4]);
       if($size = preg_match_all("/([\w]{26,})/si", $value[4], $wrapme))
       {
        $wrapme = array_shift($wrapme);
        $value[4] = strtr($value[4], array_combine($wrapme, array_map('wordwrap', $wrapme, array_fill(0, $size, 15), array_fill(0, $size, '&shy;'), array_fill(0, $size, true))));
       }
       echo('<strong>' . $value[3] . '</strong> am <strong>' . $value[1] . '</strong> um <strong>' . $value[2] . "</strong>:<br />\n" . (($smilies) ? strtr($value[4], $smilies) : $value[4]) . "<br />\n" . (($_SESSION['dispall']) ? '<div style="white-space:nowrap;">IP: <strong>' . $value[0] . '</strong> - [ <a href="' . $_SERVER['PHP_SELF'] . '?action=deleteshout&amp;id=' . $key . "#shoutbox\">Delete</a> ]</div>\n" : '') . "<hr noshade=\"noshade\" />\n");
      }
}
?>