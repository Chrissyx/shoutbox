<?php
 /**
 * Adminmodul zum Installieren und Verwalten der Shoutbox.
 * 
 * @author Chrissyx
 * @copyright (c) 2006 - 2009 by Chrissyx
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ Creative Commons 3.0 by-nc-sa
 * @package CHS_Newsscript
 * @version 0.9.11
 */
if(!is_dir('../shoutbox/')) die('<b>ERROR:</b> Konnte Verzeichnis &quot;shoutbox&quot; nicht finden!');
elseif(!file_exists('../shoutbox.php')) die('<b>ERROR:</b> Konnte &quot;shoutbox.php&quot; nicht finden!');
elseif(!file_exists('style.css')) die('<b>ERROR:</b> Konnte &quot;style.css&quot; nicht finden!');
else require('functions.php');

if(file_exists('settings.dat'))
{
 if(!$_SESSION['shoutpw'])
 {
  header('Location: ../shoutbox.php?action=admin');
  exit();
 }
 else
 {
  $settings = array_map('trim', file('settings.dat'));
  $pw = file_get_contents('../' . $settings[3]) or die('<b>ERROR:</b> Passwort nicht gefunden!');
  $action = 'admin';
 }
}
else
{
 if(decoct(fileperms($temp = basename($_SERVER['PHP_SELF']))) != '100775') chmod($temp, 0775) or die('<b>ERROR:</b> Konnte für &quot;' . $temp . '&quot; keine Rechte setzen!');
 elseif(decoct(fileperms('../shoutbox.php')) != '100775') chmod('../shoutbox.php', 0775) or die('<b>ERROR:</b> Konnte für &quot;shoutbox.php&quot; keine Rechte setzen!');
 elseif(decoct(fileperms('../shoutbox/')) != '40775') chmod('../shoutbox/', 0775) or die('<b>ERROR:</b> Konnte für den Ordner &quot;shoutbox&quot; keine Rechte setzen!');
 clearstatcache();
}

switch($action)
{
 case 'install':
 head(null, 'CHS - Shoutbox: Installation', 'Shoutbox, CHS, Installation, Chrissyx', 'Installation der Shoutbox von CHS');
 echo("  Starte Installation...<br />\n");
 if(($_POST['shoutboxdat'] && $_POST['shoutarchivdat'] && $_POST['shoutpwdat'] && $_POST['shoutmax'] && $_POST['shoutpw']) && ($_POST['shoutpw'] == $_POST['shoutpw2']))
 {
  $temp = fopen('settings.dat', 'w');
  fwrite($temp, $_POST['shoutboxdat'] . "\n" . $_POST['shoutarchivmax'] . "\n" . $_POST['shoutarchivdat'] . "\n" . $_POST['shoutpwdat'] . "\n" . $_POST['shoutmax'] . "\n" . $_POST['shoutsmilies'] . "\n" . $_POST['smiliesmax']. "\n" . $_POST['smiliesmaxrow'] . "\n" .  $_POST['redir']);
  fclose($temp);
  $temp = fopen('../' . $_POST['shoutboxdat'], 'w');
  fclose($temp);
  $temp = fopen('../' . $_POST['shoutarchivdat'], 'w');
  fclose($temp);
  $temp = fopen('../' . $_POST['shoutpwdat'], 'w');
  fwrite($temp, md5($_POST['shoutpw']));
  fclose($temp);
  echo("  Installation abgeschlossen!<br /><br />\n");
  ?>

  Um die Shoutbox nun zu nutzen, füge diesen Code an der gewünschten Stelle in den Quelltext deiner Seite ein:<br /><br />
  <code>&lt;!-- CHS - Shoutbox --&gt;&lt;?php include('shoutbox.php'); ?&gt;&lt;!-- /CHS - Shoutbox --&gt;</code><br /><br />
  Sollte es Probleme geben, lies dir die FAQ in der Readme.txt durch oder frage zur Not im Forum unter <a href="http://www.chrissyx-forum.de.vu/" target="_blank">http://www.chrissyx-forum.de.vu/</a> nach.<br /><br />
  <a href="../shoutbox.php">Zur Shoutbox selber</a> - <a href="<?=($_POST['redir']) ? $_POST['redir'] : 'http://' . $_SERVER['SERVER_NAME'] . '/'?>">Zur Seite</a>

  <?php
 }
 else echo('  <span class="b">ERROR:</span> Bitte alle relevanten Felder korrekt ausfüllen! <a href="' . $_SERVER['PHP_SELF'] . "\">Zurück...</a>\n  ");
 tail();
 break;

 case 'admin':
 if($_SESSION['shoutpw'] != $pw || $_SESSION['dispall']) die('<b>ERROR:</b> Keine Adminrechte!');
 head(null, 'CHS - Shoutbox: Administration', 'Shoutbox, CHS, Administration, Chrissyx', 'Administration der Shoutbox von CHS');
 if($_POST['update'])
 {
  $temp = "<span style=\"color:#FF0000; font-weight:bold;\">&raquo; Bitte alle relevanten Felder korrekt ausfüllen!</span><br /><br />\n";
  if(!$_POST['shoutboxdat']) $settings[0] .= '" style="border-color:#FF0000;';
  elseif(!$_POST['shoutarchivdat']) $settings[2] .= '" style="border-color:#FF0000;';
  elseif(!$_POST['shoutpwdat']) $settings[3] .= '" style="border-color:#FF0000;';
  elseif(!$_POST['shoutmax']) $settings[4] .= '" style="border-color:#FF0000;';
  elseif($_POST['shoutpw'] == $_POST['shoutpw2'])
  {
   if($_POST['shoutpw'])
   {
    $_SESSION['shoutpw'] = md5($_POST['shoutpw']);
    $temp = fopen('../' . $settings[3], 'w');
    fwrite($temp, $_SESSION['shoutpw']);
    fclose($temp);
   }
   if($_POST['shoutboxdat'] != $settings[0]) rename('../' . $settings[0], '../' . $_POST['shoutboxdat']) or $_POST['shoutboxdat'] = $settings[0];
   if($_POST['shoutarchivdat'] != $settings[2]) rename('../' . $settings[2], '../' . $_POST['shoutarchivdat']) or $_POST['shoutarchivdat'] = $settings[2];
   if($_POST['shoutpwdat'] != $settings[3]) rename('../' . $settings[3], '../' . $_POST['shoutpwdat']) or $_POST['shoutpwdat'] = $settings[3];
   $temp = fopen('settings.dat', 'w');
   fwrite($temp, $_POST['shoutboxdat'] . "\n" . $_POST['shoutarchivmax'] . "\n" . $_POST['shoutarchivdat'] . "\n" . $_POST['shoutpwdat'] . "\n" . $_POST['shoutmax'] . "\n" . $_POST['shoutsmilies'] . "\n" . $_POST['smiliesmax']. "\n" . $_POST['smiliesmaxrow'] . "\n" .  $_POST['redir']);
   fclose($temp);
   $settings = array_map('trim', file('settings.dat'));
   $temp = "<span class=\"green\">&raquo; Neue Einstellungen gespeichert!</span><br /><br />\n";
  }
 }
 else unset($temp);
 font('4');
  ?>CHS - Shoutbox: Administration</span><br /><br />
  Hier kannst Du alle Einstellungen deiner Shoutbox einsehen und anpassen.<br /><br />
  <?=$temp?>  <form name="form" action="<?=$_SERVER['PHP_SELF']?>" method="post">
  <table>
   <tr><td colspan="2"><?php font('1'); ?>Die Einstellung &quot;Anzahl der Shouts&quot; wird erst beim nächsten Shout aktiv.</span></td></tr>
   <tr><td>Anzahl der Shouts:</td><td><input type="text" name="shoutmax" value="<?=$settings[4]?>" size="25" /></td></tr>
   <tr><td>Shouts pro Archiv:</td><td><input type="text" name="shoutarchivmax" value="<?=$settings[1]?>" size="25" /></td></tr>
   <tr><td colspan="2"></td></tr>
   <tr><td>Speicherort Shouts:</td><td><input type="text" name="shoutboxdat" value="<?=$settings[0]?>" size="25" /></td></tr>
   <tr><td>Speicherort Archiv:</td><td><input type="text" name="shoutarchivdat" value="<?=$settings[2]?>" size="25" /></td></tr>
   <tr><td>Speicherort Passwort:</td><td><input type="text" name="shoutpwdat" value="<?=$settings[3]?>" size="25" /></td></tr>
   <tr><td colspan="2"></td></tr>
   <tr><td colspan="2"><?php font('1'); ?>Falls Du dein Passwort nicht ändern willst, lasse die beiden Felder einfach frei.</span></td></tr>
   <tr><td>Passwort:</td><td><input type="password" name="shoutpw"<?php if($_POST['shoutpw'] != $_POST['shoutpw2']) echo(' style="border-color:#FF0000;"'); ?> size="25" /></td></tr>
   <tr><td>Passwort wiederholen:</td><td><input type="password" name="shoutpw2"<?php if($_POST['shoutpw'] != $_POST['shoutpw2']) echo(' style="border-color:#FF0000;"'); ?> size="25" /></td></tr>
   <tr><td colspan="2"></td></tr>
   <tr><td>Pfad zur <span class="i">smilies.var</span>:</td><td><input type="text" name="shoutsmilies" value="<?=$settings[5]?>" size="25" /></td></tr>
   <tr><td>Anzahl der Smilies:</td><td><input type="text" name="smiliesmax" value="<?=$settings[6]?>" size="25" /></td></tr>
   <tr><td>Smilies pro Reihe:</td><td><input type="text" name="smiliesmaxrow" value="<?=$settings[7]?>" size="25" /></td></tr>
   <tr><td colspan="2"></td></tr>
   <tr><td>Redir nach Login:</td><td><input type="text" name="redir" value="<?=$settings[8]?>" size="25" /></td></tr>
  </table>
  <input type="submit" value="Update!" /> <input type="reset" value="Reset" /> <input type="button" value="Logout" onclick="document.location='<?=($settings[8]) ? $settings[8] : '../shoutbox.php'?>?action=shoutout';" />
  <input type="hidden" name="update" value="true" />
  </form>
  <?php
 tail();
 break;

 default:
 head(null, 'CHS - Shoutbox: Installation', 'Shoutbox, CHS, Installation, Chrissyx', 'Installation der Shoutbox von CHS');
 ?>

  <script type="text/javascript">
  function help(data)
  {
   document.getElementById('help').firstChild.nodeValue = data;
  }
  </script>

  <?php font('4'); ?>CHS - Shoutbox: Installation</span><br /><br />
  Hier kannst Du deine Shoutbox einrichten und installieren. Bereits vorhandene Dateien werden überschrieben!<br />
  Bitte treffe folgende Einstellungen:<br /><br />
  <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
  <table onmouseout="help('Hier findest Du jeweils eine kleine Hilfe zu den Einstellungen. Aktiviere JavaScript, falls sich hier nichts ändert.');">
   <tr><td colspan="2"></td><td rowspan="16" style="background-color:yellow; width:200px;"><div id="help">Hier findest Du jeweils eine kleine Hilfe zu den Einstellungen. Aktiviere JavaScript, falls sich hier nichts ändert.</div></td></tr>
   <tr onmouseover="help('Lege hier die Anzahl der angezeigten Shouts fest, bevor sie ins Archiv verschoben werden. Je mehr Shouts, desto länger wird die Box und der benötigte Platz zum Anzeigen auf deiner Seite. Normal sind 5 bis 10 Einträge, aber das ist dir überlassen.');"><td>Anzahl der Shouts:</td><td><input type="text" name="shoutmax" value="5" size="25" /></td></tr>
   <tr onmouseover="help('Optional: Wenn Du willst, kannst Du hier angeben, ob Shouts im Archiv seitenweise angezeigt werden sollen. Falls ja, bieten sich 40 bis 50 Einträge an, es können aber auch über 100 und mehr sein.');"><td>Shouts pro Archiv:</td><td><input type="text" name="shoutarchivmax" size="25" onfocus="this.value='50';" /></td></tr>
   <tr><td colspan="2"></td></tr>
   <tr onmouseover="help('Hier speichert die Shoutbox die zuletzt geschriebenen Shouts und gibt diese dann auch aus. Braucht eigentlich nicht geändert zu werden, könnte aber relevant sein, wenn Du mehr als eine Shoutbox betreibst.');"><td>Speicherort Shouts:</td><td><input type="text" name="shoutboxdat" value="shoutbox/shoutbox.dat" size="25" /></td></tr>
   <tr onmouseover="help('Der Speicherort für die archivierten Shouts. Hierhin werden alle Shouts nach und nach verschoben, gemäß der maximalen Einstellung oben. Braucht eigentlich nicht geändert zu werden, könnte aber relevant sein, wenn Du mehr als eine Shoutbox betreibst.');"><td>Speicherort Archiv:</td><td><input type="text" name="shoutarchivdat" value="shoutbox/shoutarchiv.dat" size="25" /></td></tr>
   <tr onmouseover="help('Dein Passwort wird verschlüsselt in einer seperaten Datei gespeichert. Braucht eigentlich nicht geändert zu werden, erhöht aber die Sicherheit, wenn das Passwort nicht in der Standarddatei steht.');"><td>Speicherort Passwort:</td><td><input type="text" name="shoutpwdat" value="shoutbox/shoutpw.dat" size="25" /></td></tr>
   <tr><td colspan="2"></td></tr>
   <tr onmouseover="help('Gib hier dein Passwort an, zum späteren Verwalten der Shoutbox.');"><td>Passwort:</td><td><input type="password" name="shoutpw" size="25" /></td></tr>
   <tr onmouseover="help('Das oben angegebene Passwort bitte wiederholen zur Verifizierung.');"><td>Passwort wiederholen:</td><td><input type="password" name="shoutpw2" size="25" /></td></tr>
   <tr><td colspan="2"></td></tr>
   <tr onmouseover="help('Falls Du ein TBB V1.2.3 betreibst, kannst Du Smilies davon für deine Shoutbox nutzen. Dazu musst Du hier den Pfad zur &quot;smilies.var&quot;-Datei deines TBB1 Forums angeben.');"><td>Pfad zur <span class="i">smilies.var</span>:</td><td><input type="text" name="shoutsmilies" size="25" onfocus="this.value='forum/vars/smilies.var';" /></td></tr>
   <tr onmouseover="help('Lege hier das Limit fest, wieviele Smilies unter der Shoutbox angezeigt werden sollen. Der Wert sollte ein Vielfaches von den &quot;Smilies pro Reihe&quot; sein.');"><td>Anzahl der Smilies:</td><td><input type="text" name="smiliesmax" value="22" size="25" /></td></tr>
   <tr onmouseover="help('Bestimme hier, wieviele Smilies pro Reihe unter der Shoutbox angezeigt werden sollen. Abhängig von den Breiten der verwendeten Smilies, sollten es nicht mehr als 10 oder 11 sein.');"><td>Smilies pro Reihe:</td><td><input type="text" name="smiliesmaxrow" value="11" size="25" /></td></tr>
   <tr><td colspan="2"></td></tr>
   <tr onmouseover="help('Optional: Wenn Du dich zum Verwalten der Shoutbox einloggst, kannst Du dich gleich zur Seite weiterleiten lassen, wo die Shoutbox auch zum Einsatz kommt (z.B. http://<?=$_SERVER['SERVER_NAME']?>/); anstatt nur zur Shoutbox selber.');"><td>Redir nach Login:</td><td><input type="text" name="redir" size="25" onfocus="this.value='http://';" /></td></tr>
  </table>
  <input type="submit" value="Installieren!" onmouseover="help('Alles eingestellt? Dann los! :)');" /> <input type="reset" value="Reset" onmouseover="help('Stelle die Voreinstellungen wieder her.');" />
  <input type="hidden" name="action" value="install" />
  </form>

  <?php
 tail();
 break;
}
?>