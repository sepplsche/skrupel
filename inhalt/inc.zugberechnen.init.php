<?php

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

?>