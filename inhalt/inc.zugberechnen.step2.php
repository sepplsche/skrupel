<?php
///////////////////////////////////////////////////////////////////////////////////////////////TRAKTORSTRAHL UEBERPRUEFEN ANFANG

$zeiger = mysql_query("SELECT id,traktor_id,kox,koy,besitzer,spezialmission,spiel FROM $skrupel_schiffe where spezialmission=21 and spiel=$spiel order by id");
$schiffanzahl = mysql_num_rows($zeiger);

if ($schiffanzahl>=1) {

    for ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);

        $shid=$array["id"];
        $traktor_id=$array["traktor_id"];
        $kox=$array["kox"];
        $koy=$array["koy"];
        $besitzer=$array["besitzer"];

        $zeiger2 = mysql_query("SELECT id,kox,koy,besitzer,spiel FROM $skrupel_schiffe where id=$traktor_id and kox=$kox and koy=$koy and spiel=$spiel");
        $datensaetze = mysql_num_rows($zeiger2);

        if (!$datensaetze==1) {
            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set spezialmission=0,traktor_id=0 where id=$shid and spiel=$spiel");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////TRAKTORSTRAHL UEBERPRUEFEN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////POLITIKENDE ANFANG

$zeiger = mysql_query("DELETE FROM $skrupel_politik where optionen=1 and spiel=$spiel");
$zeiger = mysql_query("UPDATE $skrupel_politik set optionen=optionen-1 where optionen>1 and spiel=$spiel");

///////////////////////////////////////////////////////////////////////////////////////////////POLITIKENDE ENDE
///////////////////////////////////////////////////////////////////////////////////////////////PLASMASTURM VERSCHWINDEN ANFANG

$zeiger = mysql_query("SELECT * FROM $skrupel_anomalien where spiel=$spiel and (art=4 or art=6) order by id");
$datensaetze = mysql_num_rows($zeiger);
if ($datensaetze>=1) {
    for ($i=0; $i<$datensaetze;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $aid=$array["id"];
        $art=$array["art"];
        $plasma_lang=$array["extra"];
        $plasma_lang--;
        if ($plasma_lang>=1) {
            $zeiger_temp = mysql_query("UPDATE $skrupel_anomalien set extra='$plasma_lang' where id=$aid");
        } else {
            if($plasma_lang==0){
                $zeiger_temp = mysql_query("DELETE FROM $skrupel_anomalien where id=$aid");
            }else{}
        }
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////PLASMASTURM VERSCHWINDEN ENDE

///////////////////////////////////////////////////////////////////////////////////////////////PLASMASTURM ENSTEHUNG ANFANG

$zeiger = mysql_query("SELECT count(*) as total FROM $skrupel_anomalien where art=6 and spiel=$spiel");
$array = mysql_fetch_array($zeiger);
$sturm=$array["total"];

$zufall=mt_rand(1,100);

if (($sturm<$plasma_max) and ($zufall<=$plasma_wahr)) {
     $x=mt_rand(1,(($umfang-310)/10));
     $y=mt_rand(1,(($umfang-310)/10));
    for($i=0;$i< 31;$i++){
        for($j=0;$j< 31;$j++){
            $abstand=round(sqrt(((15-$i)*(15-$i))+((15-$j)*(15-$j))));
            $zufall=mt_rand(1,100);
            if($zufall<=(100-($abstand*5))){
                $zeiger2 = mysql_query("SELECT extra from $skrupel_anomalien where x_pos=($x+$i)*10 and y_pos=($y+$j)*10 and art=4 and spiel=$spiel");
                $reihen = mysql_num_rows($zeiger2);
                if($reihen>=1){
                    $array2=mysql_fetch_array($zeiger2);
                    $zeit=$array["extra"];
                    if($zeit==-1){
                    }else{
                        $runden=mt_rand(3,$plasma_lang);
                        $plasma_lang_max=max($runden,$plasma_lang_max);
                        $runden=max(3,$runden,$zeit);
                        $zeiger_temp = mysql_query("INSERT INTO $skrupel_anomalien (art,x_pos,y_pos,extra,spiel) values (4,($x+$i)*10,($y+$j)*10,'$runden',$spiel)");
                    }
                }else{
                    $runden=mt_rand(3,$plasma_lang);
                    $plasma_lang_max=max($runden,$plasma_lang_max);
                    $zeiger_temp = mysql_query("INSERT INTO $skrupel_anomalien (art,x_pos,y_pos,extra,spiel) values (4,($x+$i)*10,($y+$j)*10,'$runden',$spiel)");
                }
            }
        }
    }
    $zeiger_temp = mysql_query("INSERT INTO $skrupel_anomalien (art,extra,spiel) values (6,'$plasma_lang_max',$spiel)");
}

///////////////////////////////////////////////////////////////////////////////////////////////PLASMASTURM ENSTEHUNG ENDE

///////////////////////////////////////////////////////////////////////////////////////////////NEBELSEKTOREN ANFANG
$besitzer_recht[1]='1000000000';
$besitzer_recht[2]='0100000000';
$besitzer_recht[3]='0010000000';
$besitzer_recht[4]='0001000000';
$besitzer_recht[5]='0000100000';
$besitzer_recht[6]='0000010000';
$besitzer_recht[7]='0000001000';
$besitzer_recht[8]='0000000100';
$besitzer_recht[9]='0000000010';
$besitzer_recht[10]='0000000001';

include(INCLUDEDIR.'inc.host_nebel.php');

///////////////////////////////////////////////////////////////////////////////////////////////NEBELSEKTOREN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////TARNER VERFOLGEN ANFANG

$zeiger = mysql_query("SELECT besitzer,zielid,id FROM $skrupel_schiffe where flug=3 and spiel=$spiel order by id");
$schiffanzahl = mysql_num_rows($zeiger);

if ($schiffanzahl>=1) {

    for ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);

        $array = mysql_fetch_array($zeiger);
        $ssid=$array["id"];
        $besitzer=$array["besitzer"];
        $zielid=$array["zielid"];

        $spalte='sicht_'.$besitzer.'_beta';



        $zeiger2 = mysql_query("SELECT id FROM $skrupel_schiffe where id=$zielid and tarnfeld>=1 and $spalte=0");
        $zielanzahl = mysql_num_rows($zeiger2);

        if ($zielanzahl==1) {
            $zeigertemp = mysql_query("update $skrupel_schiffe set flug=0,zielx=0,ziely=0,zielid=0 where id=$ssid");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////TARNER VERFOLGEN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////MINEN SICHTBAR ANFANG
if ($module[2]) {

    $zeiger_temp = mysql_query("UPDATE $skrupel_anomalien set sicht='0000000000' where (art=5) and spiel=$spiel");

    $zeiger = mysql_query("SELECT * FROM $skrupel_anomalien where spiel=$spiel and art=5 order by id");
    $datensaetze = mysql_num_rows($zeiger);
    if ($datensaetze>=1) {
        for ($i=0; $i<$datensaetze;$i++) {
            $ok = mysql_data_seek($zeiger,$i);
            $array = mysql_fetch_array($zeiger);
            $aid=$array["id"];
            $kox=$array["x_pos"];
            $koy=$array["y_pos"];
            $extra=$array["extra"];

            $extrab=explode(":",$extra);
            $sicht='0000000000';

            for ($xn=1;$xn<=10;$xn++) {
                if ($spieler_id_c[$xn]>=1) {
                    $ja[$xn]=0;
                    if ($extrab[0]==$xn) { $ja[$xn]=1; }
                    if (($beziehung[$xn][$extrab[0]]['status']==4) or ($beziehung[$xn][$extrab[0]]['status']==5)) { $ja[$xn]=1; }

                }
            }

            /////////////////////

             $reichweite=161;

            $zeiger_temp = mysql_query("SELECT kox,koy,id,scanner,spiel,besitzer FROM $skrupel_schiffe where besitzer!=$extrab[0] and (sqrt(((kox-$kox)*(kox-$kox))+((koy-$koy)*(koy-$koy)))<=$reichweite) and spiel=$spiel order by id");
            $scanschiff = mysql_num_rows($zeiger_temp);

            if ($scanschiff>=1) {

                for ($k=0; $k<$scanschiff;$k++) {
                    $ok2 = mysql_data_seek($zeiger_temp,$k);

                    $array_temp = mysql_fetch_array($zeiger_temp);
                    $t_shid=$array_temp["id"];
                    $t_scanner=$array_temp["scanner"];

                    $t_zielx=$array_temp["kox"];
                    $t_ziely=$array_temp["koy"];
                    $t_besitzer=$array_temp["besitzer"];

                    $lichtjahre=sqrt(($kox-$t_zielx)*($kox-$t_zielx)+($koy-$t_ziely)*($koy-$t_ziely));

                    if ((($lichtjahre<=93) and (t_scanner==0)) or (($lichtjahre<=130) and (t_scanner==1)) or (($lichtjahre<=161) and (t_scanner==2))) {
                        $ja[$t_besitzer]=1;

                        for ($xn=1;$xn<=10;$xn++) {
                            if ($spieler_id_c[$xn]>=1) {
                                if (($beziehung[$xn][$t_besitzer]['status']==4) or ($beziehung[$xn][$t_besitzer]['status']==5)) { $ja[$xn]=1; }
                            }
                        }
                    }
                }
            }

            ////////////////

            for ($xn=1;$xn<=10;$xn++) {
                if ($spieler_id_c[$xn]>=1) {
                    if ($ja[$xn]==1) { $sicht=sichtaddieren($sicht,$besitzer_recht[$xn]); }
                }
            }

            $zeiger_temp = mysql_query("UPDATE $skrupel_anomalien set sicht='$sicht' where id=$aid and spiel=$spiel");
        }
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////MINEN SICHTBAR ENDE
///////////////////////////////////////////////////////////////////////////////////////////////RANGLISTE ANFANG

for ($m=1;$m<11;$m++) {
    $spieler_basen_c[$m]=0;
    $spieler_planeten_c[$m]=0;
    $spieler_schiffe_c[$m]=0;
    $spieler_basen_c_wert[$m]=0;
    $spieler_planeten_c_wert[$m]=0;
    $spieler_schiffe_c_wert[$m]=0;
    $spieler_raus_c_old[$m]=$spieler_raus_c[$m];
    $spieler_raus_c[$m]=1;
    if ($spieler_id_c[$m]==0) { $spieler_raus_c[$m]=0; }
    $heimatplaneten[$m]=0;
}

$zeiger = mysql_query("SELECT id,besitzer,spiel,heimatplanet FROM $skrupel_planeten where spiel=$spiel and besitzer>=1 order by id");
$planetenanzahl = mysql_num_rows($zeiger);
if ($planetenanzahl>=1) {
    for ($i=0; $i<$planetenanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $besitzer=$array["besitzer"];
        $heimatplanet=$array["heimatplanet"];

        if (($heimatplanet>=1) and ($besitzer==$heimatplanet)) { $heimatplaneten[$heimatplanet]=1;}

        $spieler_planeten_c[$besitzer]=$spieler_planeten_c[$besitzer]+5;
    }
}

$zeiger = mysql_query("SELECT id,besitzer,spiel FROM $skrupel_sternenbasen where spiel=$spiel and besitzer>=1 order by id");
$planetenanzahl = mysql_num_rows($zeiger);
if ($planetenanzahl>=1) {
    for ($i=0; $i<$planetenanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $besitzer=$array["besitzer"];

        $spieler_basen_c[$besitzer]=$spieler_basen_c[$besitzer]+10;
    }
}

$zeiger = mysql_query("SELECT id,besitzer,techlevel,spiel FROM $skrupel_schiffe where spiel=$spiel and besitzer>=1 order by id");
$planetenanzahl = mysql_num_rows($zeiger);
if ($planetenanzahl>=1) {
    for ($i=0; $i<$planetenanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $besitzer=$array["besitzer"];
        $techlevel=$array["techlevel"];

        $spieler_schiffe_c[$besitzer]=$spieler_schiffe_c[$besitzer]+$techlevel;
    }
}

for ($m=1;$m<11;$m++) {
    $spieler_schiffe_platz_c[$m]=platz_schiffe($spieler_schiffe_c[$m]);
}
for ($m=1;$m<11;$m++) {
    $spieler_schiffe_c_wert[$m]=$spieler_schiffe_c[$m];
    $spieler_schiffe_c[$m]=$spieler_schiffe_platz_c[$m];
    $spieler_basen_platz_c[$m]=platz_basen($spieler_basen_c[$m]);
}
for ($m=1;$m<11;$m++) {
    $spieler_basen_c_wert[$m]=$spieler_basen_c[$m];
    $spieler_basen_c[$m]=$spieler_basen_platz_c[$m];
    $spieler_planeten_platz_c[$m]=platz_planeten($spieler_planeten_c[$m]);
}
for ($m=1;$m<11;$m++) {
    $spieler_planeten_c_wert[$m]=$spieler_planeten_c[$m];
    $spieler_planeten_c[$m]=$spieler_planeten_platz_c[$m];
    $spieler_gesamt_c[$m]=$spieler_schiffe_c[$m]+$spieler_basen_c[$m]+$spieler_planeten_c[$m];
}
for ($m=1;$m<11;$m++) {
    $spieler_platz_c[$m]=platz($spieler_gesamt_c[$m]);
}


for ($m=1;$m<11;$m++) {

    if ($spiel_out==0) {
        if (($spieler_planeten_c_wert[$m]>=1) or ($spieler_schiffe_c_wert[$m]>=1)) {
            $spieler_raus_c[$m]=0;
        }
    }
    if ($spiel_out==1) {
        if ($spieler_planeten_c_wert[$m]>=1) {
            $spieler_raus_c[$m]=0;
        }
    }
    if ($spiel_out==2) {
        if ($spieler_basen_c_wert[$m]>=1) {
            $spieler_raus_c[$m]=0;
        }
    }
    if ($spiel_out==3) {
        if ($heimatplaneten[$m]==1) {
            $spieler_raus_c[$m]=0;
        }
    }
}

$spieleranzahl=0;
for ($m=1;$m<11;$m++) {
    if (($spieler_id_c[$m]>=1) and ($spieler_raus_c[$m]==0)) { $spieleranzahl++; }
}

for ($m=1;$m<11;$m++) {
    if (($spieler_raus_c[$m]==1) and ($spieler_raus_c_old[$m]==0)) {

        $zeiger = mysql_query("SELECT besitzer,id,spiel FROM $skrupel_sternenbasen where besitzer=$m and spiel=$spiel order by id");
        $basenanzahl = mysql_num_rows($zeiger);
        if ($basenanzahl>=1) {

            for ($i=0; $i<$basenanzahl;$i++) {
                $ok = mysql_data_seek($zeiger,$i);

                $array = mysql_fetch_array($zeiger);
                $baid=$array["id"];

                $zeiger_temp = mysql_query("DELETE FROM $skrupel_huellen where baid=$baid;");
            }
        }
        $zeiger = mysql_query("UPDATE $skrupel_sternenbasen set besitzer=0 where besitzer=$m and spiel=$spiel");

        $zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where besitzer=$m and spiel=$spiel");
        $schiffanzahl = mysql_num_rows($zeiger);
        if ($schiffanzahl>=1) {
            for ($i=0; $i<$schiffanzahl;$i++) {
                $ok = mysql_data_seek($zeiger,$i);
                $array = mysql_fetch_array($zeiger);
                $shid=$array["id"];

                $zeiger_temp = mysql_query("DELETE FROM $skrupel_anomalien where art=3 and extra like 's:$shid:%'");
                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where flug=3 and zielid=$shid");
            }
        }
        $zeiger = mysql_query("DELETE FROM $skrupel_schiffe where besitzer=$m and spiel=$spiel");

        $zeiger = mysql_query("DELETE FROM $skrupel_politik where spiel=$spiel and (partei_a=$m or partei_b=$m)");

        $zeiger = mysql_query("UPDATE $skrupel_planeten set kolonisten=0,besitzer=0, auto_minen=0,auto_fabriken=0,abwehr=0,auto_abwehr=0,auto_vorrat=0,logbuch='' where besitzer=$m and spiel=$spiel");
        $zeiger = mysql_query("UPDATE $skrupel_planeten set kolonisten_new=0, schwerebt_new=0, leichtebt_new=0,kolonisten_spieler=0 where kolonisten_spieler=$m and spiel=$spiel");

        $zeiger = mysql_query("DELETE FROM $skrupel_neuigkeiten where spieler_id=$m and spiel_id=$spiel");
    }
}

$zeiger_temp = mysql_query("UPDATE $skrupel_spiele set spieleranzahl=$spieleranzahl,spieler_1_raus=$spieler_raus_c[1],spieler_2_raus=$spieler_raus_c[2],spieler_3_raus=$spieler_raus_c[3],spieler_4_raus=$spieler_raus_c[4],spieler_5_raus=$spieler_raus_c[5],spieler_6_raus=$spieler_raus_c[6],spieler_7_raus=$spieler_raus_c[7],spieler_8_raus=$spieler_raus_c[8],spieler_9_raus=$spieler_raus_c[9],spieler_10_raus=$spieler_raus_c[10],spieler_1_basen=$spieler_basen_c[1],spieler_1_planeten=$spieler_planeten_c[1],spieler_1_schiffe=$spieler_schiffe_c[1],spieler_2_basen=$spieler_basen_c[2],spieler_2_planeten=$spieler_planeten_c[2],spieler_2_schiffe=$spieler_schiffe_c[2],spieler_3_basen=$spieler_basen_c[3],spieler_3_planeten=$spieler_planeten_c[3],spieler_3_schiffe=$spieler_schiffe_c[3],spieler_4_basen=$spieler_basen_c[4],spieler_4_planeten=$spieler_planeten_c[4],spieler_4_schiffe=$spieler_schiffe_c[4],spieler_5_basen=$spieler_basen_c[5],spieler_5_planeten=$spieler_planeten_c[5],spieler_5_schiffe=$spieler_schiffe_c[5],spieler_6_basen=$spieler_basen_c[6],spieler_6_planeten=$spieler_planeten_c[6],spieler_6_schiffe=$spieler_schiffe_c[6],spieler_7_basen=$spieler_basen_c[7],spieler_7_planeten=$spieler_planeten_c[7],spieler_7_schiffe=$spieler_schiffe_c[7],spieler_8_basen=$spieler_basen_c[8],spieler_8_planeten=$spieler_planeten_c[8],spieler_8_schiffe=$spieler_schiffe_c[8],spieler_9_basen=$spieler_basen_c[9],spieler_9_planeten=$spieler_planeten_c[9],spieler_9_schiffe=$spieler_schiffe_c[9],spieler_10_basen=$spieler_basen_c[10],spieler_10_planeten=$spieler_planeten_c[10],spieler_10_schiffe=$spieler_schiffe_c[10],spieler_1_platz=$spieler_platz_c[1],spieler_2_platz=$spieler_platz_c[2],spieler_3_platz=$spieler_platz_c[3],spieler_4_platz=$spieler_platz_c[4],spieler_5_platz=$spieler_platz_c[5],spieler_6_platz=$spieler_platz_c[6],spieler_7_platz=$spieler_platz_c[7],spieler_8_platz=$spieler_platz_c[8],spieler_9_platz=$spieler_platz_c[9],spieler_10_platz=$spieler_platz_c[10] where id=$spiel");


///////////////////////////////////////////////////////////////////////////////////////////////RANGLISTE ENDE
///////////////////////////////////////////////////////////////////////////////////////////////HASH ANFANG
for ($m=1;$m<11;$m++) {

    $hash=zufallstring();
    $zeiger_temp = mysql_query("UPDATE $skrupel_spiele set spieler_".$m."_hash = '$hash' where id=$spiel");
    $spieler_hash[$m]=$hash;
}
///////////////////////////////////////////////////////////////////////////////////////////////HASH ENDE
?>
