A Installation der Skrupel XStats
-------------------------------
A1. Entpacken
A2. Tabelle anlegen


A1. Entpacken
-------------
*Zip entpacken nach skrupel/extend/xstats, folgende Dateistuktur muss nach dem Entpacken existieren:
/skrupel
/skrupel/admin
/skrupel/bilder
/skrupel/daten
/skrupel/extend
/skrupel/extend/xstats
/skrupel/extend/xstats/amcharts-php <dir>
/skrupel/extend/xstats/phplot <dir>
/skrupel/extend/xstats/DisplaySingleGame.php
/skrupel/extend/xstats/DisplaySingleGameUtil.php
/skrupel/extend/xstats/graph.php
/skrupel/extend/xstats/index.php
/skrupel/extend/xstats/readme.txt
/skrupel/extend/xstats/xstatsCollect.php
/skrupel/extend/xstats/xstatsDeleteGame.php
/skrupel/extend/xstats/xstatsInitialize.php
/skrupel/extend/xstats/xstatsUtil.php


A2. Tabelle anlegen
------------------
Zum Browser wechseln und einmal die URL 
http://<host>/extend/xstats/xstatsInitialize.php
ausfuehren. Es gibt eine positive Meldung, dass alles angelegt wurde:
"Skrupel XStats initialized successfully."

Wenn man die URL mehrfach ausfuehrt, ist das kein Problem, weil er dann sagt, dass die Tabelle schon existiert:
"Skrupel XStats already initialized - no action performed."



B Anzeige der Stats
--------------------
Eine Liste der Spiele kann man unter der URL
http://<host>/extend/xstats/index.php
sehen. Es werden dort alle Spiele angezeigt, die Statistiken und Uebersichten sieht man aber
nur fuer beendete.
