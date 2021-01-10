######################################
#Chrissyx Homepage Scripts - Shoutbox#
######################################


Version: 0.9.11


Vorwort
Die Shoutbox ist schnell, schlank und benutzerfreundlich. Alles ist einstellbar; von der Anzahl der gezeigten
Shouts, �ber Anzahl der Smilies (auch pro Reihe!) bis hin zu den Speicherorten der internen Systemdateien.
Archivierte Shouts werden seperat gespeichert f�r eine besonders schnelle Anzeige der neusten Shouts und
optional durch eine ebenfalls einstellbare Seitenanzeige im Archiv angezeigt. Die Box ist durchgehend schlank
gehalten, d.h. zu lange Links werden verk�rzt dargestellt und ebenfalls lange W�rter werden bei Bedarf mit
Trennstrich(en) umgebrochen. Nutzer eines TBB1 Forums k�nnen ihre Smilies direkt in die Shoutbox einbinden
und vom Forum aus gewohnt verwalten. Alle Einstellungen k�nnen jederzeit in der eigenen Adminoberfl�che
eingesehen und ver�ndert werden, gecachte Einstellungen werden dabei automatisch erneuert und sofort wirksam.


Vorraussetzungen
-PHP ab 4.3
-chmod f�higer Webspace


Installation
Die Installation ist gewohnt einfach: Lade in dem Ordner, wo deine Webseite ist (auf welcher die Shoutbox zum
Einsatz kommen soll), die "shoutbox.php" und den Ordner "shoutbox" samt Inhalt hoch. Rufe danach die "index.php"
aus dem Ordner "shoutbox" auf und folge dann den Anweisungen.


Update auf neue Version
Lade, wie schon zur Installation auch, alle Dateien hoch und ersetze so jede Datei durch ihre neue Version.


FAQ
-Wie kann ich meine Shoutbox verwalten?
Rufe, wie schon bei der Installation auch, die "index.php" im "shoutbox"-Ordner auf und folge den Anweisungen.

-Ich erhalte beim Aufruf die Meldung "ERROR: Datei/Ordner nicht gefunden!"?!?
Lies dir die Installationsanleitung hier genaustens durch!

-Ich erhalte beim Aufruf die Meldung "ERROR: Konnte keine Rechte setzen!"?!?
Setze mit deinem FTP Programm per chmod Befehl die Rechte auf "775" f�r die/den angegebene/n Datei/Ordner.

-Es kommt beim Aufruf eine "Warning: session_start(): Cannot send session cache limiter" Warnung?!?
F�ge ganz am Anfang deiner Seite (also noch vor "<html>" bzw. "<!DOCTYPE..."), auf welcher Du die Shoutbox
eingesetzt hast, das ein:
<?php session_start(); ?>

-Wie ist das jetzt mit den Smilies?
Smilie-Support ist grunds�tzlich vorhanden, aber bis jetzt bedient sich die Shoutbox bei den Smilies eines TBB1
(Tritanium Bulletin Board). Falls Du tats�chlich solch ein Forum nutzt, kannst Du auch deine Shoutbox mit Smilies
betreiben, in dem Du bei der Installation die relevanten Felder ausf�llst. Die Smilies werden dann wie gewohnt
vom Forum aus verwaltet. In der kommenden V1.0 bringt die Shoutbox dann eine eigene Verwaltung f�r Smilies mit,
ohne auf ein TBB angewiesen zu sein.
Mehr Infos zum TBB unter http://www.tritanium-scripts.com/


Credits
� 2006 - 2009 by Chrissyx
Powered by V4 Technology
http://www.chrissyx.de(.vu)/
http://www.chrissyx.com/