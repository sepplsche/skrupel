<?php

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
///////////////////////////////////////////////////////////////////////////////////////////////ROUTESTARTEN ANFANG

$zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where flug=0 and status=2 and routing_status=2 and spiel=$spiel order by id");
$schiffanzahl = mysql_num_rows($zeiger);
if ($schiffanzahl>=1) {
    for  ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);

        $besitzer=$array["besitzer"];
        $volk=$array["volk"];
        $shid=$array["id"];
        $name=$array["name"];
        $bild_gross=$array["bild_gross"];
        $routing_id=$array["routing_id"];
        $routing_koord=$array["routing_koord"];
        $routing_schritt=$array["routing_schritt"];
        $routing_warp=$array["routing_warp"];
        $routing_mins=$array["routing_mins"];
        $routing_mins_temp=explode(":",$routing_mins);
        $mins=$routing_mins_temp[$routing_schritt];
        $mins_cantox=substr($mins,0,1);
        $mins_vorrat=substr($mins,1,1);
        $mins_lemin=substr($mins,2,1);
        $mins_min1=substr($mins,3,1);
        $mins_min2=substr($mins,4,1);
        $mins_min3=substr($mins,5,1);
        $leuts_kol=(int)substr($mins,7,7);
        $leuts_lbt=(int)substr($mins,14,4);
        $leuts_sbt=(int)substr($mins,18,4);
        $frachtraum=$array["frachtraum"];
        $leichtebt=$array["leichtebt"];
        $schwerebt=$array["schwerebt"];
        $fracht_leute=$array["fracht_leute"];
        $fracht_cantox=$array["fracht_cantox"];
        $fracht_vorrat=$array["fracht_vorrat"];
        $fracht_min1 = $array["fracht_min1"];
        $fracht_min2 = $array["fracht_min2"];
        $fracht_min3 = $array["fracht_min3"];
        $voll_laden=substr($mins,6,1);

        if(($voll_laden!=1)or($mins_vorrat==1)or($mins_min1==1)or($mins_min2==1)or($mins_min3==1)or($leuts_kol==1)or($leuts_kol>2)or($leuts_lbt==1)or($leuts_lbt>2)or($leuts_sbt==1)or($leuts_sbt>2)){
             if(($voll_laden!=1)or((round(($fracht_leute/100)+($leichtebt*0.3)+($schwerebt*1.5)+0.5)+$fracht_vorrat+$fracht_min1+$fracht_min2+$fracht_min3)>=$frachtraum)){
                $routing_points_temp=explode("::",$routing_koord);
                if ($routing_schritt==count($routing_points_temp)-2) {
                    $routing_schritt=0;} else {$routing_schritt++;
                }
                $routing_points=explode(":",$routing_points_temp[$routing_schritt]);
                $routing_id_temp=explode(":",$routing_id);
                $zielx=$routing_points[0];
                $ziely=$routing_points[1];
                $warp=$routing_warp;
                $zielid=$routing_id_temp[$routing_schritt];
                $zeigertemp = mysql_query("update $skrupel_schiffe set flug=2,warp=$warp,zielx=$zielx,ziely=$ziely,zielid=$zielid,routing_schritt=$routing_schritt where id=$shid");
            } else {
                $zeigertemp = mysql_query("update $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where id=$shid");
            }
        } else {
            neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['flug'][9],array($name));
            $zeigertemp = mysql_query("update $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0,routing_schritt=0,routing_koord='',routing_warp=0,routing_mins='',routing_id='',routing_tank=0,routing_status=0 where id=$shid");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////ROUTESTARTEN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////MINENAKTION LOESCHEN BEI BEWEGUNG ANFANG

if ($module[2]) {
    $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set spezialmission=0 where spiel=$spiel and flug>=1 and (spezialmission=24 or spezialmission=25)");
}

///////////////////////////////////////////////////////////////////////////////////////////////MINENAKTION LOESCHEN BEI BEWEGUNG ENDE
///////////////////////////////////////////////////////////////////////////////////////////////TRAKTORSTRAHL UEBERPRUEFEN ANFANG

$zeiger = mysql_query("SELECT id,traktor_id,besitzer,warp FROM $skrupel_schiffe where spezialmission=21 and spiel=$spiel order by id");
while($array = mysql_fetch_array($zeiger)) {
    list($shid, $traktor_id, $besitzer, $warp) = $array;

    if ($warp>7) {
        mysql_query("UPDATE $skrupel_schiffe SET warp=7 WHERE id=$shid AND spiel=$spiel");
    }

    $zeiger2 = mysql_query("SELECT flug,besitzer,spezialmission FROM $skrupel_schiffe WHERE id=$traktor_id AND spiel=$spiel");
    $array2 = mysql_fetch_array($zeiger2);
    list($flug, $besitzer2, $spezialmission) = $array2;

    if ($flug>0 || $spezialmission>0 || $besitzer!=$besitzer2) {
        mysql_query("UPDATE $skrupel_schiffe SET spezialmission=0,traktor_id=0 WHERE id=$shid AND spiel=$spiel");
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////TRAKTORSTRAHL UEBERPRUEFEN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////SCHIFFSUEBERGABE ANFANG

$zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where spezialmission>=31 and spezialmission<=40 and !(volk='unknown' and klasseid=1) and spiel=$spiel order by id");
while($array = mysql_fetch_array($zeiger)) {
    $shid=$array['id'];
    $name=$array['name'];
    $volk=$array['volk'];
    $besitzer=$array['besitzer'];
    $bild_gross=$array['bild_gross'];
    $spezialmission=$array['spezialmission'];

    $neu_besitzer = $spezialmission-30;

    $neu_nick_besitzer = nick($spieler_id_c[$neu_besitzer]);
    $nick_besitzer = nick($spieler_id_c[$besitzer]);

    neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['uebergabe'][0],array($name,'<font color='.$spielerfarbe[$neu_besitzer].'>'.$neu_nick_besitzer.'</font>'));
    neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$neu_besitzer,$lang['host'][$spielersprache[$neu_besitzer]]['uebergabe'][1],array('<font color='.$spielerfarbe[$besitzer].'>'.$nick_besitzer.'</font>',$name));
    mysql_query("UPDATE $skrupel_schiffe set spezialmission=0,besitzer=$neu_besitzer,fracht_leute=0,schwerebt=0,leichtebt=0,ordner=0 where id=$shid");
}

///////////////////////////////////////////////////////////////////////////////////////////////SCHIFFSUBERGABE ENDE
///////////////////////////////////////////////////////////////////////////////////////////////PLASMASTURM - SCHIFFE ANFANG

$zeiger = mysql_query("SELECT * FROM $skrupel_anomalien where spiel=$spiel and art=4 order by id");
$datensaetze = mysql_num_rows($zeiger);
if ($datensaetze>=1) {
    for  ($i=0; $i<$datensaetze;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $x_pos=$array["x_pos"];
        $y_pos=$array["y_pos"];
        $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set spezialmission=0 where (spezialmission=13 or spezialmission=11 or spezialmission=12 or spezialmission=8 or spezialmission=7 or spezialmission=9 or spezialmission=10) and kox>=$x_pos and kox<=$x_pos+10 and koy>=$y_pos and koy<=$y_pos+10 and zusatzmodul<>9 and spiel=$spiel");
        $zeiger_temp = mysql_query("SELECT id,warp,plasmawarp FROM $skrupel_schiffe where warp>5 and kox>=$x_pos and kox<=$x_pos+10 and koy>=$y_pos and koy<=$y_pos+10 and zusatzmodul<>9 and spiel=$spiel order by id");
        $datensaetze_temp = mysql_num_rows($zeiger_temp);
        if ($datensaetze_temp>=1) {
            for  ($j=0; $j<$datensaetze_temp;$j++) {
                $ok = mysql_data_seek($zeiger_temp,$j);
                $array_temp = mysql_fetch_array($zeiger_temp);
                $shid = $array_temp["id"];
                $warp = $array_temp["warp"];
                $plasmawarp = $array_temp["plasmawarp"];
                $plasmawarp = max(0,$warp,$plasmawarp);
                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set plasmawarp=$plasmawarp,warp=5 where spiel=$spiel and id=$shid");
            }
        }
    }
}
$zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where spiel=$spiel and plasmawarp<>0 order by id");
$datensaetze = mysql_num_rows($zeiger);
if ($datensaetze>=1) {
    for  ($i=0; $i<$datensaetze;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $kox=$array["kox"];
        $koy=$array["koy"];
        $plasmawarp=$array["plasmawarp"];
        $zeiger_temp = mysql_query("SELECT * FROM $skrupel_anomalien where art=4 and x_pos<=$kox and x_pos>=$kox-10 and y_pos<=$koy and y_pos>=$koy-10 and spiel=$spiel order by id");
        $datensaetze_temp = mysql_num_rows($zeiger_temp);
        if ($datensaetze_temp>=1){

        } else {
            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set warp=$plasmawarp, plasmawarp=0 where spiel=$spiel and id=$shid");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////PLASMASTURM - SCHIFFE ENDE

$planetenerobert=0; // benutzt in inc.host_bodenkampf.php und inc.host_spionage.php
$planetenerobertfehl=0; // benutzt in inc.host_bodenkampf.php und inc.host_spionage.php

///////////////////////////////////////////////////////////////////////////////////////////////BODENKAMPF ANFANG

$zeiger = mysql_query("SELECT * FROM $skrupel_planeten where kolonisten_spieler>=1 and ((kolonisten_new>=1) or (leichtebt_new>=1) or (schwerebt_new>=1)) and besitzer>=1 and spiel=$spiel order by id");
$planetenanzahl = mysql_num_rows($zeiger);
if ($planetenanzahl>0) {
    include(INCLUDEDIR.'inc.host_bodenkampf.php');
}

///////////////////////////////////////////////////////////////////////////////////////////////BODENKAMPF ENDE
///////////////////////////////////////////////////////////////////////////////////////////////WELLENGENERATOR ANFANG

$reichweite = 65;
$zeiger = mysql_query("SELECT * FROM $skrupel_schiffe WHERE spezialmission=70 and spiel=$spiel order by id");
while($array = mysql_fetch_array($zeiger)) {
    $shid = $array["id"];
    $name = $array["name"];
    $kox = $array["kox"];
    $koy = $array["koy"];
    $volk = $array["volk"];
    $besitzer = $array["besitzer"];
    $bild_gross = $array["bild_gross"];
    $vomisaan = $array["fracht_min3"];
    $fertigkeiten=$array["fertigkeiten"];
    $wellengenerator_fert=intval(substr($fertigkeiten,60,1));

    if ($wellengenerator_fert>=1 && $vomisaan >= $wellengenerator_fert) {

        $erfolg = false;
        $zeiger_temp = mysql_query("SELECT * FROM $skrupel_schiffe WHERE (sqrt(((kox-$kox)*(kox-$kox))+((koy-$koy)*(koy-$koy)))<=$reichweite) and spiel=$spiel order by id");
        while($array_temp = mysql_fetch_array($zeiger_temp)) {
            $t_shid = $array_temp["id"];
            $t_name = $array_temp["name"];
            $t_volk = $array_temp["volk"];
            $t_besitzer = $array_temp["besitzer"];
            $t_bild_gross = $array_temp["bild_gross"];
            $t_spezialmission = $array_temp["spezialmission"];
            $t_warp = $array_temp["warp"];
            $zielx = $array_temp["kox"];
            $ziely = $array_temp["koy"];

            $lichtjahre2 = ($kox-$zielx)*($kox-$zielx)+($koy-$ziely)*($koy-$ziely);
            if($lichtjahre2 <= $reichweite*$reichweite) {
                if(($t_spezialmission!=7 && $t_spezialmission!=16) || $t_besitzer==$besitzer || ($beziehung[$besitzer][$t_besitzer]['status']>=3 && $beziehung[$besitzer][$t_besitzer]['status']<=5)) {
                    if($t_warp > 7) {
                        neuigkeiten(2,"../daten/$t_volk/bilder_schiffe/$t_bild_gross",$t_besitzer,$lang['host'][$spielersprache[$t_besitzer]]['wellengenerator'][2],array($t_name));
                        mysql_query("UPDATE $skrupel_schiffe set warp=7 where id=$t_shid");
                    }
                } else {
                    $erfolg = true;
                    neuigkeiten(2,"../daten/$t_volk/bilder_schiffe/$t_bild_gross",$t_besitzer,$lang['host'][$spielersprache[$t_besitzer]]['wellengenerator'][0],array($t_name));
                    mysql_query("UPDATE $skrupel_schiffe set spezialmission=0,warp=0,flug=0,zielx=0,ziely=0,zielid=0 where id=$t_shid");
                }
            }
        }
        $vomisaan -= $wellengenerator_fert;
        mysql_query("UPDATE $skrupel_schiffe set fracht_min3=$vomisaan where id=$shid");
        if ($erfolg) {
            neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['wellengenerator'][3],array($name));
        }
    } else {
        neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['wellengenerator'][1],array($name));
        mysql_query("UPDATE $skrupel_schiffe set spezialmission=0 where id=$shid");
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////WELLENGENERATOR ENDE
///////////////////////////////////////////////////////////////////////////////////////////////SPRUNGTRIEBWERK ANFANG
$schiffverschollen=0;
$zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where flug>=1 and flug<=2 and status>0 and spezialmission=7 and spiel=$spiel order by id");
$schiffanzahl = mysql_num_rows($zeiger);
if ($schiffanzahl>=1) {
    for  ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $name=$array["name"];
        $kox=$array["kox"];
        $koy=$array["koy"];
        $flug=$array["flug"];
        $zielx=$array["zielx"];
        $ziely=$array["ziely"];
        $volk=$array["volk"];
        $besitzer=$array["besitzer"];
        $bild_gross=$array["bild_gross"];
        $lemin=$array["lemin"];

        $fertigkeiten=$array["fertigkeiten"];
        $spezialmission=$array["spezialmission"];
        $status=$array["status"];

        $fert_sprung_kosten=intval(substr($fertigkeiten,11,3));
        $fert_sprung_min=intval(substr($fertigkeiten,14,4));
        $fert_sprung_max=intval(substr($fertigkeiten,18,4));

        if ($lemin>=$fert_sprung_kosten) {
            $lichtjahre=sqrt(($kox-$zielx)*($kox-$zielx)+($koy-$ziely)*($koy-$ziely));
            $reichweite=mt_rand($fert_sprung_min,$fert_sprung_max);
            $faktor=$reichweite/$lichtjahre;
            $strecke_x=($zielx-$kox)*$faktor;
            $strecke_y=($ziely-$koy)*$faktor;
            $kox_neu=$kox+$strecke_x;
            $koy_neu=$koy+$strecke_y;
            $lemin=$lemin-$fert_sprung_kosten;

            $rand_x_a = max(min($kox,$kox_neu),1);
            $rand_x_b = min(max($kox,$kox_neu),$umfang);
            $rand_y_a = max(min($koy,$koy_neu),1);
            $rand_y_b = min(max($koy,$koy_neu),$umfang);
            $zeiger_temp = mysql_query("SELECT * FROM $skrupel_schiffe WHERE kox>=$rand_x_a and kox<=$rand_x_b and koy>=$rand_y_a and koy<=$rand_y_b and spezialmission=70 and spiel=$spiel order by kox ".($kox<$kox_neu?"asc":"desc").", koy ".($koy<$koy_neu?"asc":"desc"));
            while($array_temp = mysql_fetch_array($zeiger_temp)) {
                $t_kox = $array_temp["kox"];
                $t_koy = $array_temp["koy"];
                $t_besitzer = $array_temp["besitzer"];
                $t_name = $array_temp["name"];
                $t_volk = $array_temp["volk"];
                $t_bild_gross = $array_temp["bild_gross"];
                if ($t_besitzer != $besitzer && $beziehung[$besitzer][$t_besitzer]['status'] < 3) {
                    $co1 = (($t_kox-$kox)*($kox_neu-$kox)+($t_koy-$koy)*($koy_neu-$koy));
                    $c_ = sqrt(($t_kox-$kox)*($t_kox-$kox)+($t_koy-$koy)*($t_koy-$koy));
                    $a_ = sin(acos($co1/(($c_*$reichweite)+1)))*$c_;
                    if ($a_ <= 65) {
                        $reichweite2 = sqrt($c_*$c_ - $a_*$a_);
                        $faktor = $reichweite2/$lichtjahre;
                        $kox_neu = intval($kox+($zielx-$kox)*$faktor);
                        $koy_neu = intval($koy+($ziely-$koy)*$faktor);
                        neuigkeiten(2,"../daten/$t_volk/bilder_schiffe/$t_bild_gross",$t_besitzer,$lang['host'][$spielersprache[$t_besitzer]]['wellengenerator'][4],array($t_name));
                        neuigkeiten(2,"../bilder/news/sprung.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['sprungtriebwerk'][3],array($name,(int)$reichweite2));
                        $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set spezialmission=0,kox=$kox_neu, koy=$koy_neu, lemin=$lemin, flug=0, status=1 where id=$shid");
                        continue(2);
                    }
                }
            }
            if (($kox_neu>=10) and ($kox_neu<=$umfang-13) and ($koy_neu>=10) and ($koy_neu<=$umfang-13)) {
                neuigkeiten(2,"../bilder/news/sprung.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['sprungtriebwerk'][0],array($name,$reichweite));
                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set spezialmission=0,kox=$kox_neu, koy=$koy_neu, lemin=$lemin, flug=0, status=1 where id=$shid");
            } else {
                $schiffverschollen++;
                neuigkeiten(2,"../bilder/news/sprung.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['sprungtriebwerk'][1],array($name));
                $zeiger_temp = mysql_query("DELETE FROM $skrupel_schiffe where id=$shid");
                $zeiger_temp = mysql_query("DELETE FROM $skrupel_anomalien where art=3 and extra like 's:$shid:%'");
                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where flug=3 and zielid=$shid");
            }
        } else {
            neuigkeiten(2,"../bilder/news/sprung.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['sprungtriebwerk'][2],array($name));
            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set spezialmission=0,flug=0 where id=$shid");
        }
    }
}
mysql_query("UPDATE $skrupel_zugberechnen_daten set schiffverschollen=$schiffverschollen WHERE sid='$sid'");
///////////////////////////////////////////////////////////////////////////////////////////////SPRUNGTRIEBWERK ANFANG
///////////////////////////////////////////////////////////////////////////////////////////////SUBRAUMVERZERRUNG ANFANG

$zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where spezialmission=9 and spiel=$spiel order by id");
$schiffanzahl = mysql_num_rows($zeiger);
if ($schiffanzahl>=1) {
    for  ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $name=$array["name"];
        $klasse=$array["klasse"];
        $antrieb=$array["antrieb"];
        $klasseid=$array["klasseid"];
        $kox=$array["kox"];
        $koy=$array["koy"];
        $volk=$array["volk"];
        $besitzer=$array["besitzer"];
        $bild_gross=$array["bild_gross"];
        $fertigkeiten=$array["fertigkeiten"];
        $fert_subver=intval(substr($fertigkeiten,23,1));
        $sub_schaden=$fert_subver*50;

        $zeiger_temp = mysql_query("SELECT * FROM $skrupel_schiffe where (sqrt((($kox-kox)*($kox-kox))+(($koy-koy)*($koy-koy)))<=83) and spezialmission<>9 and spiel=$spiel order by id");
        $treffschiff = mysql_num_rows($zeiger_temp);
        if ($treffschiff>=1) {
            for ($k=0; $k<$treffschiff;$k++) {
                $ok2 = mysql_data_seek($zeiger_temp,$k);
                $array_temp = mysql_fetch_array($zeiger_temp);
                $t_shid=$array_temp["id"];
                $t_name=$array_temp["name"];
                $t_klasse=$array_temp["klasse"];
                $t_antrieb=$array_temp["antrieb"];
                $t_klasseid=$array_temp["klasseid"];
                $t_volk=$array_temp["volk"];
                $t_besitzer=$array_temp["besitzer"];
                $t_bild_gross=$array_temp["bild_gross"];
                $t_schaden=$array_temp["schaden"];
                $t_masse=$array_temp["masse"];
                $zielx=$array_temp["kox"];
                $ziely=$array_temp["koy"];

                $schaden=round($t_schaden+($sub_schaden*(80/($t_masse+1))*(80/($t_masse+1))+2));
                if ($schaden<100) {
                    neuigkeiten(2,"../daten/$t_volk/bilder_schiffe/$t_bild_gross",$t_besitzer,$lang['host'][$spielersprache[$t_besitzer]]['subraumverzerrer'][0],array($t_name,$schaden));
                    $zeiger_temp2 = mysql_query("UPDATE $skrupel_schiffe set schaden=$schaden where id=$t_shid");
                }
                if ($schaden>=100) {
                    neuigkeiten(2,"../daten/$t_volk/bilder_schiffe/$t_bild_gross",$t_besitzer,$lang['host'][$spielersprache[$t_besitzer]]['subraumverzerrer'][1],array($t_name));
                    $zeiger_temp2 = mysql_query("DELETE FROM $skrupel_schiffe where id=$t_shid");
                    $zeiger_temp2 = mysql_query("DELETE FROM $skrupel_anomalien where art=3 and extra like 's:$t_shid:%'");
                    $zeiger_temp2 = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where (flug=3 or flug=4) and zielid=$t_shid");
                }
            }
        }

        $zeiger_temp = mysql_query("SELECT * FROM $skrupel_anomalien where (sqrt(($kox-x_pos)*($kox-x_pos)+($koy-y_pos)*($koy-y_pos))<=83) and art=3 and spiel=$spiel order by id");
        $trefffalte = mysql_num_rows($zeiger_temp);
        if ($trefffalte>=1) {
            for ($k=0; $k<$trefffalte;$k++) {
                $ok2 = mysql_data_seek($zeiger_temp,$k);
                $array_temp = mysql_fetch_array($zeiger_temp);
                $fid=$array_temp["id"];
                $war=mt_rand(1,10);
                if($war<=$fert_subver){
                    $zeiger_temp2 = mysql_query("DELETE FROM $skrupel_anomalien where id=$fid");
                }
            }
        }

        neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['subraumverzerrer'][2],array($name));
        $zeiger_temp2 = mysql_query("DELETE FROM $skrupel_schiffe where id=$shid");
        $zeiger_temp2 = mysql_query("DELETE FROM $skrupel_anomalien where art=3 and extra like 's:$shid:%'");
        $zeiger_temp2 = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where (flug=3 or flug=4) and zielid=$shid");
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////SUBRAUMVERZERRUNG ENDE
///////////////////////////////////////////////////////////////////////////////////////////////LOYDS FLUCHTMANOEVER ANFANG

$zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where spezialmission=16 and spiel=$spiel order by id");
$schiffanzahl = mysql_num_rows($zeiger);

if ($schiffanzahl>=1) {

    for  ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);

        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $name=$array["name"];
        $volk=$array["volk"];
        $schaden=$array["schaden"];
        $besitzer=$array["besitzer"];
        $bild_gross=$array["bild_gross"];
        $spezialmission=$array["spezialmission"];
        $fertigkeiten=$array["fertigkeiten"];
        $s_x=$array["s_x"];
        $s_y=$array["s_y"];
        $fluchtmanoever=intval(substr($fertigkeiten,38,2));
        $kox=$s_x;
        $koy=$s_y;

        if ($fluchtmanoever==1) {
            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set spezialmission=0,kox=$kox,koy=$koy,flug=0,warp=0,zielx=0,ziely=0,zielid=0 where id=$shid");
            $zeiger_temp2 = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where (flug=3 or flug=4) and zielid=$shid");
            neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['fluchtmanoever'][0],array($name));
        }
        if ($fluchtmanoever>=2) {
            $schadenbumm=mt_rand(1,$fluchtmanoever);
            $schaden=$schaden+$schadenbumm;
            if ($schaden<100) {
                neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['fluchtmanoever'][1],array($name,$schadenbumm));
                $zeiger_temp2 = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where (flug=3 or flug=4) and zielid=$shid");
                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set schaden=$schaden,spezialmission=0,kox=$kox,koy=$koy,flug=0,warp=0,zielx=0,ziely=0,zielid=0 where id=$shid");
            }
            if ($schaden>=100) {
                neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['fluchtmanoever'][2],array($name));
                $zeiger_temp2 = mysql_query("DELETE FROM $skrupel_schiffe where id=$shid");
                $zeiger_temp2 = mysql_query("DELETE FROM $skrupel_anomalien where art=3 and extra like 's:$shid:%'");
                $zeiger_temp2 = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where (flug=3 or flug=4) and zielid=$shid");
            }
        }
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////LOYDS FLUCHTMANOEVER ENDE
?>