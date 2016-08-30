<?php
include ('inc.host_func.php');

if ($main_verzeichnis!='../') $main_verzeichnis='';
define('DATADIR', $main_verzeichnis.'daten/');
define('INCLUDEDIR', $main_verzeichnis.'inhalt/');
define('LANGUAGEDIR', $main_verzeichnis.'lang/');

$xstats_verzeichnis = $main_verzeichnis.'extend/xstats';

if (@intval(substr($spiel_extend,1,1))==1) {
    include("../extend/ki/ki_basis/berechneKI.php");
}
if ((@file_exists($xstats_verzeichnis)) and (intval(substr($spiel_extend,2,1))==1)) {
    include($xstats_verzeichnis.'/xstatsCollect.php');
}
///////////////////////////////Sprachinclude(nur die benoetigten) Anfang
$zeiger = mysql_query("SELECT * FROM $skrupel_spiele WHERE id=$spiel");
$sprachtemp_1 = mysql_fetch_array($zeiger);

$sprachen = array();
for ($i=1; $i<=10; $i++){
    $spieler = 'spieler_'.$i;
    if($sprachtemp_1[$spieler] > 0) {
        $zeiger = mysql_query("SELECT * FROM $skrupel_user WHERE id={$sprachtemp_1[$spieler]}");
        $sprachtemp_3 = mysql_fetch_array($zeiger);
        $spielersprache[$i] = ($sprachtemp_3['sprache']=='') ? $language : $sprachtemp_3['sprache'];

        if (in_array($sprachtemp_3['sprache'], $sprachen)) $sprachen[] = $sprachtemp_3['sprache'];
    }
}

if(count($sprachen) == 0) {
    include(LANGUAGEDIR.$language.'/lang.inc.host.php');
} else {
    foreach($sprachen as $sprache) {
        include(LANGUAGEDIR.$sprache.'/lang.inc.host.php');
    }
}
///////////////////////////////Sprachinclude(nur die benoetigten) Ende

srand((double)microtime()*1000000);
mt_srand(time());
$mt_randmax=mt_getrandmax();

$schiffverschollen=0;
$neuekolonie=0;
$neueschiffe=0;
$neuebasen=0;
$schiffevernichtet=0;
$planetenerobert=0;
$planetenerobertfehl=0;

mysql_query("DELETE FROM $skrupel_kampf WHERE spiel=$spiel");
mysql_query("DELETE FROM $skrupel_nebel WHERE spiel=$spiel");
mysql_query("DELETE FROM $skrupel_scan WHERE spiel=$spiel");
mysql_query("DELETE FROM $skrupel_neuigkeiten WHERE sicher=0 AND spiel_id=$spiel AND (art<=4 OR art=7 OR art=8)");

///////////////////////////////////////////////////////////////////////////////////////////////RASSENEIGENSCHAFTEN ANFANG

$handle = opendir(DATADIR);

if ($handle) {
    while ($rasse = readdir($handle)) {
        if (is_dir(DATADIR.$rasse) && is_file(DATADIR.$rasse.'/daten.txt')) {
            $rassendaten = rasse_laden(DATADIR.$rasse.'/daten.txt');
            if ($rassendaten) $r_eigenschaften[$rasse] = $rassendaten;
        }
    }
    closedir($handle);
}

///////////////////////////////////////////////////////////////////////////////////////////////RASSENEIGENSCHAFTEN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////SPIELEREIGENSCHAFTEN ANFANG

for ($k=1; $k<=10; $k++) {
    $s_eigenschaften[$k]['rasse']=$spieler_rasse_c[$k];
}

///////////////////////////////////////////////////////////////////////////////////////////////SPIELEREIGENSCHAFTEN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////BEGEGNUNGEN ANFANG

if ($module[4]==1) {
    $begegnungen = array();
    $zeiger = mysql_query("SELECT partei_a,partei_b FROM $skrupel_begegnung where spiel=$spiel");
    $polanzahl = mysql_num_rows($zeiger);
    if ($polanzahl>=1) {
        for  ($i=0; $i<$polanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $partei_a=$array["partei_a"];
        $partei_b=$array["partei_b"];

        $begegnung[$partei_a][$partei_b]=1;
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////BEGEGNUNGEN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////WAFFENWERTE ANFANG

$strahlenschaden = array ('0','3','7','10','15','12','29','35','37','18','45');
$strahlenschadencrew = array ('0','1','2','2','4','16','7','8','9','33','11');
$torpedoschaden = array ('0','5','8','10','6','15','30','35','12','48','55');
$torpedoschadencrew = array ('0','1','2','2','13','6','7','8','36','12','14');

///////////////////////////////////////////////////////////////////////////////////////////////WAFFENWERTE ENDE
///////////////////////////////////////////////////////////////////////////////////////////////STATS INITIALISIEREN ANFANG

$stat_sieg = array_fill(1, 10, 0);
$stat_schlacht = array_fill(1, 10, 0);
$stat_schlacht_sieg = array_fill(1, 10, 0);
$stat_kol_erobert = array_fill(1, 10, 0);
$stat_lichtjahre = array_fill(1, 10, 0);

///////////////////////////////////////////////////////////////////////////////////////////////STATS INITIALISIEREN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////POLITIKSTATUS ANFANG

//tabelle initialisieren
$beziehung = array_fill(1, 10, array_fill(1, 10, array('status'=>0, 'optionen'=>0)));

$zeiger = mysql_query("SELECT partei_a,partei_b,status,optionen FROM $skrupel_politik WHERE spiel=$spiel");
while($array = mysql_fetch_array($zeiger)) {
    list($partei_a, $partei_b, $status, $optionen) = $array;

    $beziehung[$partei_a][$partei_b]['status']   = $status;
    $beziehung[$partei_b][$partei_a]['status']   = $status;
    $beziehung[$partei_a][$partei_b]['optionen'] = $optionen;
    $beziehung[$partei_b][$partei_a]['optionen'] = $optionen;
}

///////////////////////////////////////////////////////////////////////////////////////////////POLITIKSTATUS ENDE
?>
