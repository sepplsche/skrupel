<?php
///////////////////////////////Sprachinclude(nur die benoetigten) Anfang
define('LANGUAGEDIR', $main_verzeichnis.'lang/');

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
///////////////////////////////////////////////////////////////////////////////////////////////STATS INITIALISIEREN ANFANG

$stat_sieg = array_fill(1, 10, 0);
$stat_schlacht = array_fill(1, 10, 0);
$stat_schlacht_sieg = array_fill(1, 10, 0);
$stat_kol_erobert = array_fill(1, 10, 0);
$stat_lichtjahre = array_fill(1, 10, 0);

///////////////////////////////////////////////////////////////////////////////////////////////STATS INITIALISIEREN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////LETZTER MONAT ANFANG
$zeiger = mysql_query("SELECT * FROM $skrupel_zugberechnen_daten WHERE sid='$sid'");
$array = mysql_fetch_array($zeiger);

$neuekolonie = $array['neuekolonie'];
$neueschiffe = $array['neueschiffe'];
$neuebasen = $array['neuebasen'];
$schiffevernichtet = $array['schiffevernichtet'];
$planetenerobert = $array['planetenerobert'];
$planetenerobertfehl = $array['planetenerobertfehl'];
$schiffverschollen = $array['schiffverschollen'];

if ($neuekolonie==0) {$neuekolonie=$lang['host'][$language]['letztermonat'][0];}
if ($neuekolonie==1) {$neuekolonie=$lang['host'][$language]['letztermonat'][1];}
if ($neuekolonie>=2) {$neuekolonie=str_replace(array('{1}'),array($neuekolonie),$lang['host'][$language]['letztermonat'][2]);}

if ($neueschiffe==0) {$neueschiffe=$lang['host'][$language]['letztermonat'][3];}
if ($neueschiffe==1) {$neueschiffe=$lang['host'][$language]['letztermonat'][4];}
if ($neueschiffe>=2) {$neueschiffe=str_replace(array('{1}'),array($neueschiffe),$lang['host'][$language]['letztermonat'][5]);}

if ($neuebasen==0) {$neuebasen=$lang['host'][$language]['letztermonat'][6];}
if ($neuebasen==1) {$neuebasen=$lang['host'][$language]['letztermonat'][7];}
if ($neuebasen>=2) {$neuebasen=str_replace(array('{1}'),array($neuebasen),$lang['host'][$language]['letztermonat'][8]);}

if ($schiffevernichtet==0) {$schiffevernichtet=$lang['host'][$language]['letztermonat'][9];}
if ($schiffevernichtet==1) {$schiffevernichtet=$lang['host'][$language]['letztermonat'][10];}
if ($schiffevernichtet>=2) {$schiffevernichtet=str_replace(array('{1}'),array($schiffevernichtet),$lang['host'][$language]['letztermonat'][11]);}

if ($planetenerobert==0) {$planetenerobert=$lang['host'][$language]['letztermonat'][12];}
if ($planetenerobert==1) {$planetenerobert=$lang['host'][$language]['letztermonat'][13];}
if ($planetenerobert>=2) {$planetenerobert=str_replace(array('{1}'),array($planetenerobert),$lang['host'][$language]['letztermonat'][14]);}

if ($planetenerobertfehl==0) {$planetenerobertfehl="";}
if ($planetenerobertfehl==1) {$planetenerobertfehl=$lang['host'][$language]['letztermonat'][15];}
if ($planetenerobertfehl>=2) {$planetenerobertfehl=str_replace(array('{1}'),array($planetenerobertfehl),$lang['host'][$language]['letztermonat'][16]);}

if ($schiffverschollen==0) {$schiffverschollen=$lang['host'][$language]['letztermonat'][21];}
if ($schiffverschollen==1) {$schiffverschollen=$lang['host'][$language]['letztermonat'][22];}
if ($schiffverschollen>=2) {$schiffverschollen=str_replace(array('{1}'),array($schiffverschollen),$lang['host'][$language]['letztermonat'][23]);}


$letztermonat=str_replace(array('{1}','{2}','{3}','{4}','{5}','{6}','{7}'),array($neuekolonie,$neueschiffe,$neuebasen,$schiffevernichtet,$planetenerobert,$planetenerobertfehl,$schiffverschollen),$lang['host'][$language]['letztermonat'][17]);

$zeiger_temp = mysql_query("UPDATE $skrupel_spiele set letztermonat='$letztermonat', runde=runde+1 where id=$spiel;");


///////////////////////////////////////////////////////////////////////////////////////////////ZIEL ANFANG

$endejetzt=0;

if ($ziel_id==1) {
    if ($spieleranzahl<=intval($ziel_info)) {
        $endejetzt=1;
    }
}
if ($ziel_id==2) {
    if (($spieler_raus_c[1]==1) or ($spieler_raus_c[5]==1) or ($spieler_raus_c[8]==1) or ($spieler_raus_c[2]==1) or ($spieler_raus_c[6]==1) or ($spieler_raus_c[9]==1) or ($spieler_raus_c[3]==1) or ($spieler_raus_c[7]==1) or ($spieler_raus_c[10]==1) or ($spieler_raus_c[4]==1)) {
        $endejetzt=1;
    }
}

if ($ziel_id==5) {

    for ($k=1;$k<11;$k++) {
        $spieler_ziel_t_c[$k]=0;
    }

    $zeiger = mysql_query("SELECT status,spiel,id,besitzer,fracht_min3 FROM $skrupel_schiffe where status<>2 and spiel=$spiel order by id");
    $schiffanzahl = mysql_num_rows($zeiger);

    if ($schiffanzahl>=1) {
        for ($i=0; $i<$schiffanzahl;$i++) {
            $ok = mysql_data_seek($zeiger,$i);
            $array = mysql_fetch_array($zeiger);
            $besitzer=$array["besitzer"];
            $fracht_min3=$array["fracht_min3"];

            if ($fracht_min3>=1) { $spieler_ziel_t_c[$besitzer]=$spieler_ziel_t_c[$besitzer]+$fracht_min3; }
        }
    }

    $zeiger_temp = mysql_query("UPDATE $skrupel_spiele set spieler_1_ziel='$spieler_ziel_t_c[1]',spieler_2_ziel='$spieler_ziel_t_c[2]',spieler_3_ziel='$spieler_ziel_t_c[3]',spieler_4_ziel='$spieler_ziel_t_c[4]',spieler_5_ziel='$spieler_ziel_t_c[5]',spieler_6_ziel='$spieler_ziel_t_c[6]',spieler_7_ziel='$spieler_ziel_t_c[7]',spieler_8_ziel='$spieler_ziel_t_c[8]',spieler_9_ziel='$spieler_ziel_t_c[9]',spieler_10_ziel='$spieler_ziel_t_c[10]' where id=$spiel;");
    $temp=intval($ziel_info);

    if (($spieler_ziel_t_c[1]>=$temp) or ($spieler_ziel_t_c[2]>=$temp) or ($spieler_ziel_t_c[3]>=$temp) or ($spieler_ziel_t_c[4]>=$temp) or ($spieler_ziel_t_c[5]>=$temp) or ($spieler_ziel_t_c[6]>=$temp) or ($spieler_ziel_t_c[7]>=$temp) or ($spieler_ziel_t_c[8]>=$temp) or ($spieler_ziel_t_c[9]>=$temp) or ($spieler_ziel_t_c[10]>=$temp)) {
        $endejetzt=1;
    }
}

if ($ziel_id==6) {
    if (($spieler_raus_c[1]==1) or ($spieler_raus_c[5]==1) or ($spieler_raus_c[8]==1) or ($spieler_raus_c[2]==1) or ($spieler_raus_c[6]==1) or ($spieler_raus_c[9]==1) or ($spieler_raus_c[3]==1) or ($spieler_raus_c[7]==1) or ($spieler_raus_c[10]==1) or ($spieler_raus_c[4]==1)) {
        $endejetzt=1;
    }
}


if ($endejetzt==1) {
    include(INCLUDEDIR.'inc.host_spielende.php');
}

///////////////////////////////////////////////////////////////////////////////////////////////ZIEL ENDE
///////////////////////////////////////////////////////////////////////////////////////////////STATS AUSWERTUNG ANFANG

for ($m=1;$m<11;$m++) {
    if ($spieler_id_c[$m]>=1) {$zeiger = mysql_query("UPDATE $skrupel_user set stat_sieg=stat_sieg+$stat_sieg[$m],stat_schlacht=stat_schlacht+$stat_schlacht[$m],stat_schlacht_sieg=stat_schlacht_sieg+$stat_schlacht_sieg[$m],stat_kol_erobert=stat_kol_erobert+$stat_kol_erobert[$m],stat_lichtjahre=stat_lichtjahre+$stat_lichtjahre[$m],stat_monate=stat_monate+1 where id=$spieler_id_c[$m]"); }
}
if ((@file_exists($xstats_verzeichnis)) and (intval(substr($spiel_extend,2,1))==1)) {
    xstats_collectAndStore( $sid, &$stat_schlacht,&$stat_schlacht_sieg,&$stat_kol_erobert,&$stat_lichtjahre);
}
///////////////////////////////////////////////////////////////////////////////////////////////STATS AUSWERTUNG ENDE
/////////////////////////////////////////////////////////////////////////////////////////////// BENACHRICHTIGUNG ANFANG


for ($k=1; $k<=10; $k++) {
    if ($spieler_id_c[$k]>0 and $spieler_raus_c[$k]==0) {
        $nachrichtemail=str_replace('{1}',$spiel_name,$lang['host'][$spielersprache[$k]]['letztermonat'][18]);
        $nachrichticq=str_replace('{1}',$spiel_name,$lang['host'][$spielersprache[$k]]['letztermonat'][24]);
        $emailtopic=str_replace(array('{1}'),array($spiel_name),$lang['host'][$spielersprache[$k]]['letztermonat'][20]);

        $zeiger = mysql_query("SELECT * FROM $skrupel_user WHERE id={$spieler_id_c[$k]}");
        $array = mysql_fetch_array($zeiger);
        $emailadresse=$array['email'];
        $icqnummer=$array['icq'];
        $optionen=$array['optionen'];
        $emailicq=$icqnummer."@pager.icq.com";


        $url="http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
        $url=substr($url,0,strlen($url)-19);
        
        $url="http://".$_SERVER['SERVER_NAME'];
        $folders = explode('/', $_SERVER['SCRIPT_NAME']);
        $count = 0;
        $url .= '/';
        foreach ($folders as $value) {
            if ((0 < $count) and (count($folders) > $count+1) and ('inhalt' != $value)){
                $url .= $value . '/';
            }
            $count++;
        }        
        $hash=$spieler_hash[$k];

        $nachricht_fertig = $nachrichtemail."\n\n".$url.'index.php?hash='.$hash;

        if (substr($optionen,0,1)=='1') {
        @mail($emailadresse,$emailtopic, $nachricht_fertig,
            "From: $absenderemail\r\n"
            ."Reply-To: $absenderemail\r\n"
            ."X-Mailer: PHP/" . phpversion());
        }
        /*
        if (substr($optionen,1,1)=='1') {
            $header="From $absenderemail\nReply-To:$absenderemail\n";
            @mail($emailicq,$emailtopic,"$nachrichticq",$header);
        }
        */
    }
}
$nachricht=str_replace('{1}',$spiel_name,$lang['host'][$language]['letztermonat'][19]);
$aktuell=time();
$zeiger = mysql_query("INSERT INTO $skrupel_chat (spiel,datum,text,an,von,farbe) values ($spiel,'$aktuell','$nachricht',0,'System','000000');");


/////////////////////////////////////////////////////////////////////////////////////////////// BENACHRICHTIGUNG ENDE

///////////////////////////////////////////////////////////////////////////////////////////////LETZTER MONAT ENDE
?>
