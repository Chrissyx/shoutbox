<?php
//$action laden
$action = (!$_GET['action']) ? $_POST['action'] : $_GET['action'];

//echo Kurzform aktivieren
if(ini_get(short_open_tag) == '0') ini_set(short_open_tag, '1');

//Session laden, IP sichern
session_start();
if(!$_SESSION['session_ip']) $_SESSION['session_ip'] = $_SERVER['REMOTE_ADDR'];
else if($_SESSION['session_ip'] != $_SERVER['REMOTE_ADDR']) die('Nicht erlaubt, diese Session zu verwenden!');

//Aufbauzeit [PHP4]
$_SESSION['microtime'] = explode(' ', microtime());
$_SESSION['microtime'] = $_SESSION['microtime'][1] + $_SESSION['microtime'][0];

//Funktionen
/**
 * Generiert den XHTML Head für jede Seite und sendet den passenden Content-Type, wenn der Browser XML unterstützt.
 * 
 * @author Chrissyx
 * @copyright Chrissyx
 * @param string Zusätzliche Angaben zum <head> Tag, mit Leerzeichen beginnen!
 * @param string Der Titel des Dokuments
 * @param string Metatag für Schlüsselwörter
 * @param string Metatag für Beschreibung
 * @param string Die zu benutzende CSS Datei
 * @param string Weitere optionale XHTML Tags im Head
 * @param string Zusätzliche optionale Angaben zum <body> Tag, mit Leerzeichen beginnen!
 * @see tail()
 */
function head($htmlzusatz, $title, $keywords, $description, $stylesheet='style.css', $sonstiges=null, $bodyzusatz=null)
{
 if(stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml')) header('Content-Type: application/xhtml+xml');
 echo('<?xml version="1.0" encoding="ISO-8859-1" standalone="no" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de"' . $htmlzusatz . '>
 <head>
  <title>' . $title . '</title>
  <meta name="author" content="Chrissyx" />
  <meta name="copyright" content="Chrissyx" />
  <meta name="keywords" content="' . $keywords . '" />
  <meta name="description" content="' . $description . '" />
  <meta name="robots" content="all" />
  <meta name="revisit-after" content="14 days" />
  <meta name="generator" content="Notepad 4.10.1998" />
  <meta http-equiv="content-language" content="de" />
  <meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
  <meta http-equiv="content-style-type" content="text/css" />
  <meta http-equiv="content-script-type" content="text/javascript" />
  <link rel="stylesheet" media="all" href="' . $stylesheet . '" />
');
 if($sonstiges) echo("  $sonstiges\n");
 echo(' </head>
 <body' . $bodyzusatz . '>
  <a id="top" name="top"></a>
');
}

/**
 * Generiert abschliessende Tags eines XHTML Dokuments und zeigt die Aufbauzeit an.
 * 
 * @author Chrissyx
 * @copyright Chrissyx
 * @see head()
 */
function tail()
{
 $temp = explode(' ', microtime());
 $temp = ($temp[1] + $temp[0]) - $_SESSION['microtime'];
 echo('<div class="center" style="width:100%;">');
 font('1');
 echo('Seitenaufbauzeit: ' . round($temp, 4) . ' Sekunden</span></div>
 </body>
</html>');
}

/**
 * Gibt das CSS-Äquivalent zur HTML Schriftgröße aus. Nicht vergessen: </span>!
 * 
 * @author Chrissyx
 * @copyright Chrissyx
 * @param string HTML Schriftgröße von 1 bis 7 oder eigener Wert.
 */
function font($wert)
{
 switch($wert)
 {
  case '7':
  echo('<span style="font-size:300%;">');
  break;

  case '6':
  echo('<span style="font-size:xx-large;">');
  break;

  case '5':
  echo('<span style="font-size:x-large;">');
  break;

  case '4':
  echo('<span style="font-size:large;">');
  break;

  case '3':
  echo('<span style="font-size:medium;">');
  break;

  case '2':
  echo('<span style="font-size:small;">');
  break;

  case '1.5':
  echo('<span style="font-size:x-small;">');
  break;

  case '1':
  echo('<span style="font-size:xx-small;">');
  break;

  default:
  echo('<span style="font-size:' . $wert . ';">');
  break;
 }
}
?>