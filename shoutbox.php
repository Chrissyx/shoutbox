<?php

#####################################################################
#Script written by Chrissyx                                         #
#You may use and edit this script, if you don't remove this comment!#
#http://www.chrissyx.de(.vu)/                                       #
#####################################################################

$action = (!$_POST['action']) ? $_GET['action'] : $_POST['action'];
session_start();
$shoutboxdat = "dats/shoutbox.dat";
$shoutarchivdat = "dats/shoutarchiv.dat";
$shoutpwdat = "dats/pw.dat";
$shoutmax = 5;
$smilies = file("forum/vars/smilies.var");

//Admin Login
if ($action == "admin")
{
 $pw = @file($shoutpwdat) or die("<b>ERROR:</b> Passwort noch nicht angelegt!");
 $_SESSION['dispall'] = false;
 if (md5($_POST['shoutpw']) == $pw[0])
 {
  $_SESSION['shoutpw'] = md5($_POST['shoutpw']);
  unset($_POST['shoutpw']);
  $_SESSION['dispall'] = true;
  @header("Location: http://" . $_SERVER['SERVER_NAME'] . "/");
  die("Eingeloggt! <a href=\"http://" . $_SERVER['SERVER_NAME'] . "/\">Zurück zur Startseite...</a>");
 }
 else
 {
  ?>

 CHS - ShoutBox - LogIn<br />
 <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
 Bitte Passwort angeben: <input type="password" name="shoutpw"><br />
 <input type="submit" value="Einloggen">
 <input type="hidden" name="action" value="admin">
 </form>

  <?php
  exit();
 }
}

//Admin Logout
elseif ($action == "shoutout") unset($_SESSION['shoutpw'], $_SESSION['dispall']);

//Archiv aufrufen
if ($action == "archiv")
{
 $temp = file($shoutarchivdat);
 if (!$temp) echo("Archiv ist leer!");
 if ($_SESSION['dispall']) foreach($temp as $key => $value)
                           {
                            $value = explode("\t", $value);
                            echo("<strong>" . $value[3] . "</strong> am <strong>" . $value[1] . "</strong> um <strong>" . $value[2] . "</strong>:<br />\n" . $value[4] . "<br />\nIP: <strong>" . $value[0] . "</strong> - [ <a href=\"" . $_SERVER['PHP_SELF'] . "?action=deleteold&id=$key\">Delete</a> ]<br />\n<hr>");
                           }
 else foreach($temp as $value)
      {
       $value = explode("\t", $value);
       echo("<strong>" . $value[3] . "</strong> am <strong>" . $value[1] . "</strong> um <strong>" . $value[2] . "</strong>:<br />\n" . $value[4] . "<br />\n<hr>");
      }
}

//Aus Archiv löschen
elseif ($action == "deleteold")
{
 $pw = @file($shoutpwdat) or die("<b>ERROR:</b> Passwort noch nicht angelegt!");
 if ($_SESSION['shoutpw'] == $pw[0] && $_SESSION['dispall'])
 {
  $towrite = file($shoutarchivdat);
  if (!$towrite[$_GET['id']]) die("<b>ERROR:</b> Shout nicht gefunden!");
  array_splice($towrite, $_GET['id'], 1);
  $temp = fopen($shoutarchivdat, "w");
  fwrite($temp, implode("", $towrite));
  fclose($temp);
  @header("Location: " . $_SERVER['PHP_SELF'] . "?action=archiv");
  die("Eintrag gelöscht! <a href=\"" . $_SERVER['PHP_SELF'] . "?action=archiv\">Zurück zum Archiv...</a>");
 }
 else echo("<b>ERROR:</b> Keine Adminrechte!");
}

//Formular zeigen
else
{
 ?>

<a name="shoutbox"></a>
<form name="shoutboxform" action="<?=$_SERVER['PHP_SELF']?>#shoutbox" method="post" onMouseOver="canShout();">
<div class="nowrap">Nick: <input type="text" name="cookieName" size="17" value="<?=$_SESSION['cookieName']?>" onChange="canShout();"><br />
<textarea name="shoutbox" rows="3" cols="18"></textarea><br/ >
<?php
for ($i=0; $i<=19; $i++)
{
 $value = explode("\t", $smilies[$i]);
 if ($i == 11) echo("<br />\n");
 echo("<a href=\"javascript:setShoutSmilie(' " . $value[1] . " ');\"><img src=\"forum/" . $value[2] . "\" border=\"0\" alt=\"" . $value[1] . "\"></a>");
}
?><br />
<input type="submit" name="shout" value="Shout!" disabled> <input type="reset" value="Reset"> <input type="button" value="Archiv" onClick="window.open('shoutbox.php?action=archiv', '_blank', 'width=400, resizable, scrollbars, status');"></div>
<input type="hidden" name="action" value="shout">
<?php if($_SESSION['shoutpw']) echo("<a href=\"" . $_SERVER['PHP_SELF'] . "?action=shoutout\">Logout</a>"); ?></form>

 <?php

//Shouten
 if ($action == "shout")
 {
  $towrite = file($shoutboxdat);                                                                                                                                
  array_unshift($towrite, $_SERVER['REMOTE_ADDR'] . "\t" . date("d.m.Y") . "\t" . date("H:i:s") . "\t" . (($_POST['cookieName']) ? htmlspecialchars(stripslashes($_POST['cookieName']), ENT_QUOTES) : $_SERVER['REMOTE_ADDR']) . "\t" . ereg_replace("(\r)(\n)", "" , nl2br(htmlspecialchars(stripslashes($_POST['shoutbox']), ENT_QUOTES))) . "\n");
  while (count($towrite) > $shoutmax)
  {
   $archiv = file($shoutarchivdat);
   array_unshift($archiv, $towrite[count($towrite)-1]);
   array_pop($towrite);
   $temp = fopen($shoutarchivdat, "w");
   fwrite($temp, implode("", $archiv));
   fclose($temp);
  }
  $temp = fopen($shoutboxdat, "w");
  fwrite($temp, implode("", $towrite));
  fclose($temp);
 }

//Shouts löschen
 elseif ($action == "deleteshout")
 {
  $pw = @file($shoutpwdat) or die("<b>ERROR:</b> Passwort noch nicht angelegt!");
  if ($_SESSION['shoutpw'] == $pw[0] && $_SESSION['dispall'])
  {
   $towrite = file($shoutboxdat);
   if (!$towrite[$_GET['id']]) die("<b>ERROR:</b> Shout nicht gefunden!");
   array_splice($towrite, $_GET['id'], 1);
   $archiv = file($shoutarchivdat);
   $towrite[] = $archiv[0];
   array_shift($archiv);
   $temp = fopen($shoutarchivdat, "w");
   fwrite($temp, implode("", $archiv));
   fclose($temp);
   $temp = fopen($shoutboxdat, "w");
   fwrite($temp, implode("", $towrite));
   fclose($temp);
   echo("Shout gelöscht!<br />\n");
  }
  else echo("<b>ERROR:</b> Keine Adminrechte!<br />\n");
 }

//Shouts zeigen
 $temp = file($shoutboxdat);
 if (!$temp) echo("Keine Shouts gefunden!");
 $size = count($smilies);
 if ($_SESSION['dispall']) foreach($temp as $key => $value)
                           {
                            $value = explode("\t", $value);
                            for ($i=0; $i<=$size; $i++)
                            {
                             $smilie = explode("\t", $smilies[$i]);
                             $value[4] = str_replace($smilie[1], "<img src=\"forum/" . $smilie[2] . "\" border=\"0\" alt=\"" . $smilie[1] . "\">", $value[4]);
                            }
                            echo("<strong>" . $value[3] . "</strong> am <strong>" . $value[1] . "</strong> um <strong>" . $value[2] . "</strong>:<br />\n" . trim($value[4]) . "<br />\n<div class=\"nowrap\">IP: <strong>" . $value[0] . "</strong> - [ <a href=\"" . $_SERVER['PHP_SELF'] . "?action=deleteshout&id=$key\">Delete</a> ]</div>\n<hr>");
                           }
 else foreach($temp as $value)
      {
       $value = explode("\t", $value);
       for ($i=0; $i<=$size; $i++)
       {
        $smilie = explode("\t", $smilies[$i]);
        $value[4] = str_replace($smilie[1], "<img src=\"forum/" . $smilie[2] . "\" border=\"0\" alt=\"" . $smilie[1] . "\">", $value[4]);
       }
       echo("<strong>" . $value[3] . "</strong> am <strong>" . $value[1] . "</strong> um <strong>" . $value[2] . "</strong>:<br />\n" . trim($value[4]) . "<br />\n<hr noshade>");
      }
}
?>