<?php
/**
 * Importer for shouts from past version of Shoutbox.
 *
 * @author Chrissyx <chris@chrissyx.com>
 * @copyright (c) 2010 by Chrissyx
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ Creative Commons 3.0 by-nc-sa
 * @package CHS_Shoutbox
 * @version 1.0
 */
interface CHSModule
{
 public function execute();
}

function convertShoutFile($file)
{
 $oldShouts = array_map('trim', file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
 $newShouts = array();
 foreach($oldShouts as $value)
 {
  $value = explode("\t", $value);
  $newShouts[] = mktime(substr($value[2], 0, 2), substr($value[2], 3, 2), substr($value[2], 6, 2), substr($value[1], 3, 2), substr($value[1], 0, 2), substr($value[1], 6, 4)) . "\t" . $value[0] . "\t" . trim($value[3]) . "\t" . trim($value[4]) . "\n";
 }
 file_put_contents($file, $newShouts);
 header('Content-Disposition: attachment; filename=' . $file);
 header('Content-Type: text/plain; name=' . $file);
 readfile($file);
 unlink($file);
 exit();
}

include('../chscore/modules/CHSFunctions.php');
@CHSFunctions::execute();

if(isset($_FILES['shouts']))
{
 if($_FILES['shouts']['error'] == 0)
 {
  move_uploaded_file($_FILES['shouts']['tmp_name'], $_FILES['shouts']['name']);
  convertShoutFile($_FILES['shouts']['name']);
 }
 elseif($_FILES['shouts']['error'] == 4)
  echo(CHSFunctions::getMsgBox('Keine Datei ausgewählt!', 'yellow'));
 else
  echo(CHSFunctions::getMsgBox('Upload der Shouts fehlgeschlagen!', 'red'));
}

CHSFunctions::printHead('CHS &ndash; Shoutbox: Import', 'shoutbox, import, chrissyx', 'Import-Script für Shouts der Box 0.9.11', null, null, null, null, null, null, '../chscore/styles/style.css');
?>
  <h2>Import der alten Shouts</h2>
  <p>Hier kannst Du deine alten Shouts aus der Shoutbox 0.9.11 für die neue Version 2.0 importieren. Wähle jeweils eine DAT-Datei aus und klicke auf &quot;Import starten&quot;.</p>

  <form action="<?=$_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data">
  <h3>Datei auswählen</h3>
  <p>Import der Shouts aus der Shoutbox (z.B. <code>shoutbox/shoutbox.dat</code>) oder aus dem Archiv (z.B. <code>shoutbox/shoutarchiv.dat</code>):</p>
  <p><input type="file" name="shouts" /></p>
  <input type="submit" value="Import starten" />
  </form>
 </body>
</html>