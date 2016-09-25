<?php
///////////////////////////////////////////////////////////////////////////////////////////////ZIELKORREKTUR ANFANG
if($kreisel_anzahl>0){
    for($i=0;$i< $kreisel_anzahl;$i++){
        $zeiger2 = mysql_query("SELECT kox,koy FROM $skrupel_schiffe where spiel=$spiel and id=$zwischenarray_zielid[$i]");
        $array2 = mysql_fetch_array($zeiger2);
        $t_kox=$array2["kox"];
        $t_koy=$array2["koy"];
        $zeiger2 = mysql_query("UPDATE $skrupel_schiffe set zielx=$t_kox,ziely=$t_koy,zielid=$zwischenarray_zielid[$i] where spiel=$spiel and id=$zwischenarray_shid[$i]");
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////ZIELKORREKTUR ENDE

///////////////////////////////////////////////////////////////////////////////////////////////ERFAHRUNG DURCH STRECKE ANFANG

$zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set erfahrung=erfahrung+1,strecke=strecke-1000 where strecke>999 and erfahrung<5 and spiel=$spiel");

///////////////////////////////////////////////////////////////////////////////////////////////ERFAHRUNG DURCH STRECKE ENDE
///////////////////////////////////////////////////////////////////////////////////////////////WURMLOCH ANFANG

$zeiger = mysql_query("SELECT * FROM $skrupel_anomalien where spiel=$spiel order by id");
$datensaetze = mysql_num_rows($zeiger);
if ($datensaetze>=1) {
    for  ($i=0; $i<$datensaetze;$i++) {
    $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $aid=$array["id"];
        $art=$array["art"];
        $x_pos=$array["x_pos"];
        $y_pos=$array["y_pos"];
        $extra=$array["extra"];

        $extras=explode(":",$extra);

        if (($art==1) or ($art==2)) {

            if ($art==1) { $reichweite=15; }elseif ($art==2) { $reichweite=10; }

            $zeiger_temp = mysql_query("SELECT * FROM $skrupel_schiffe where sqrt( (kox-$x_pos)*(kox-$x_pos)+(koy-$y_pos)*(koy-$y_pos) )<=$reichweite and spiel=$spiel order by id");
            $schiffanzahl = mysql_num_rows($zeiger_temp);

            if ($schiffanzahl>=1) {

                for  ($k=0; $k<$schiffanzahl;$k++) {
                    $ok_temp = mysql_data_seek($zeiger_temp,$k);

                    $array_temp = mysql_fetch_array($zeiger_temp);
                    $shid=$array_temp["id"];
                    $bild_gross=$array_temp["bild_gross"];
                    $volk=$array_temp["volk"];
                    $s_x_pos=$array_temp["kox"];
                    $s_y_pos=$array_temp["koy"];
                    $antrieb=$array_temp["antrieb"];
                    $besitzer=$array_temp["besitzer"];
                    $name=$array_temp["name"];
                    $spezialmission=$array_temp["spezialmission"];

                    if ($extras[0]>=1) {

                        if ($spezialmission==29) {
                            $zeiger2 = mysql_query("UPDATE $skrupel_anomalien set extra='' where id=$aid");
                            $aid2=intval($extras[0]);
                            $zeiger2 = mysql_query("UPDATE $skrupel_anomalien set extra='' where id=$aid2");
                        }
                        $alpha=(double)(6.28318530718*mt_rand(0,$mt_randmax)/$mt_randmax);
                        $y=max(0,min($umfang,$extras[2]+round(($reichweite+3)*sin($alpha))));
                        $x=max(0,min($umfang,$extras[1]+round(($reichweite+3)*cos($alpha))));

                        $zeiger2 = mysql_query("UPDATE $skrupel_schiffe set kox=$x, koy=$y, zielx=0, ziely=0, flug=0, status=1  where id=$shid");
                        $zeiger_temp2 = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where (flug=3 or flug=4) and zielid=$shid");
                        if ($art==1) {
                            if ($spezialmission==29) {
                                neuigkeiten(2,"../bilder/news/wurmloch.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['wurmloch'][4],array($name));
                            } else {
                                neuigkeiten(2,"../bilder/news/wurmloch.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['wurmloch'][0],array($name));
                            }
                        }elseif ($art==2) {
                            if ($spezialmission==29) {
                                neuigkeiten(2,"../bilder/news/sprungtor.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['wurmloch'][5],array($name));
                            } else {
                                neuigkeiten(2,"../bilder/news/sprungtor.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['wurmloch'][1],array($name));
                            }
                        }
                    } else {
                        $ok=1;
                        while ($ok==1) {
                            $x=mt_rand(50,$umfang-100);
                            $y=mt_rand(50,$umfang-100);

                            $ok=2;
                            $nachbarn=0;
                            $zeiger2 = mysql_query("SELECT count(*) as total from $skrupel_planeten where sqrt( (x_pos-$x)*(x_pos-$x)+(x_pos-$x)*(x_pos-$x) )<=20 and spiel=$spiel");
                            $array = mysql_fetch_array($zeiger2);
                            $nachbarn=$array["total"];

                            if ($nachbarn>=1) {$ok=1;}
                        }
                        $zeiger2 = mysql_query("UPDATE $skrupel_schiffe set kox=$x, koy=$y, zielx=0, ziely=0, flug=0, status=1  where id=$shid");
                        $zeiger_temp2 = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where (flug=3 or flug=4) and zielid=$shid");
                        if ($art==1) {
                            neuigkeiten(2,"../bilder/news/wurmloch.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['wurmloch'][2],array($name));
                        }elseif ($art==2) {
                            neuigkeiten(2,"../bilder/news/sprungtor.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['wurmloch'][3],array($name));
                        }
                    }
                }
            }
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////WURMLOCH ENDE
///////////////////////////////////////////////////////////////////////////////////////////////DRUGUNVERZERRER ANFANG

$zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where spezialmission=30 and spiel=$spiel order by id");
$schiffanzahl = mysql_num_rows($zeiger);

if ($schiffanzahl>=1) {

    for  ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);

        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $name=$array["name"];
        $klasse=$array["klasse"];
        $klasseid=$array["klasseid"];
        $kox=$array["kox"];
        $koy=$array["koy"];
        $volk=$array["volk"];
        $besitzer=$array["besitzer"];
        $bild_gross=$array["bild_gross"];
        $reichweite=round(intval($array["masse"])/2);

        $zeiger_temp = mysql_query("SELECT * FROM $skrupel_schiffe where (sqrt(((kox-$kox)*(kox-$kox))+((koy-$koy)*(koy-$koy)))<=$reichweite) and tarnfeld=1 and spiel=$spiel order by id");
        $treffschiff = mysql_num_rows($zeiger_temp);

        if ($treffschiff>=1) {

            for ($k=0; $k<$treffschiff;$k++) {
                $ok2 = mysql_data_seek($zeiger_temp,$k);

                $array_temp = mysql_fetch_array($zeiger_temp);
                $t_shid=$array_temp["id"];
                $t_name=$array_temp["name"];
                $t_klasse=$array_temp["klasse"];
                $t_klasseid=$array_temp["klasseid"];
                $t_volk=$array_temp["volk"];
                $t_besitzer=$array_temp["besitzer"];
                $t_bild_gross=$array_temp["bild_gross"];

                neuigkeiten(2,"../daten/$t_volk/bilder_schiffe/$t_bild_gross",$t_besitzer,$lang['host'][$spielersprache[$t_besitzer]]['drugunverzerrer'][0],array($t_name));
                $zeiger_temp2 = mysql_query("UPDATE $skrupel_schiffe set tarnfeld=0 where id=$t_shid");
            }
        }
        neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['drugunverzerrer'][1],array($name));
        $zeiger_temp2 = mysql_query("DELETE FROM $skrupel_schiffe where id=$shid");
        $zeiger_temp2 = mysql_query("DELETE FROM $skrupel_anomalien where art=3 and extra like 's:$shid:%'");
        $zeiger_temp2 = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where (flug=3 or flug=4) and zielid=$shid");
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////DRUGUNVERZERRER ENDE
///////////////////////////////////////////////////////////////////////////////////////////////SELFDESTRUCT ANFANG

$zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where spezialmission=15 and spiel=$spiel order by id");
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
        $sub_schaden=intval($array["techlevel"])*50;

        $reichweite=83;

        $zeiger_temp = mysql_query("SELECT * FROM $skrupel_schiffe where (sqrt(((kox-$kox)*(kox-$kox))+((koy-$koy)*(koy-$koy)))<=$reichweite) and spezialmission<>15 and spiel=$spiel order by id");
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
                    neuigkeiten(2,"../daten/$t_volk/bilder_schiffe/$t_bild_gross",$t_besitzer,$lang['host'][$spielersprache[$t_besitzer]]['selfdestruct'][0],array($t_name,$schaden));
                    $zeiger_temp2 = mysql_query("UPDATE $skrupel_schiffe set schaden=$schaden where id=$t_shid");
                }
                if ($schaden>=100) {
                    neuigkeiten(2,"../daten/$t_volk/bilder_schiffe/$t_bild_gross",$t_besitzer,$lang['host'][$spielersprache[$t_besitzer]]['selfdestruct'][1],array($t_name));
                    $zeiger_temp2 = mysql_query("DELETE FROM $skrupel_schiffe where id=$t_shid");
                    $zeiger_temp2 = mysql_query("DELETE FROM $skrupel_anomalien where art=3 and extra like 's:$t_shid:%'");
                    $zeiger_temp2 = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where (flug=3 or flug=4) and zielid=$t_shid");
                }
            }
        }
        neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['selfdestruct'][2],array($name));
        $zeiger_temp2 = mysql_query("DELETE FROM $skrupel_schiffe where id=$shid");
        $zeiger_temp2 = mysql_query("DELETE FROM $skrupel_anomalien where art=3 and extra like 's:$shid:%'");
        $zeiger_temp2 = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where (flug=3 or flug=4) and zielid=$shid");
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////SELFDESTRUCT ENDE


///////////////////////////////////////////////////////////////////////////////////////////////RAUMFALTEN ANFANG
$zeiger = mysql_query("SELECT * FROM $skrupel_anomalien where extra like 's:%' and art=3 and spiel=$spiel order by id");
$anoanzahl = mysql_num_rows($zeiger);

if ($anoanzahl>=1) {

    for  ($i=0; $i<$anoanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $anoid=$array["id"];
        $extra=$array["extra"];

        $extras=explode(":",$extra);

        $zeiger_temp = mysql_query("SELECT id,kox,koy,spiel FROM $skrupel_schiffe where  spiel=$spiel and id=".$extras[1]." order by id");
        $array_temp = mysql_fetch_array($zeiger_temp);
        $kox=$array_temp["kox"];
        $koy=$array_temp["koy"];

        $optionen="s:".$extras[1].":$kox:$koy:".$extras[4].":".$extras[5].":".$extras[6].":".$extras[7].":".$extras[8].":".$extras[9];
        $zeiger_temp = mysql_query("UPDATE $skrupel_anomalien set extra='$optionen' where spiel=$spiel and id=$anoid");

    }
}

$zeiger = mysql_query("SELECT * FROM $skrupel_anomalien where  art=3 and spiel=$spiel order by id");
$anoanzahl = mysql_num_rows($zeiger);

if ($anoanzahl>=1) {

    for  ($i=0; $i<$anoanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $anoid=$array["id"];
        $extra=$array["extra"];
        $kox=$array["x_pos"];
        $koy=$array["y_pos"];

        $extras=explode(":",$extra);
        $zielx=$extras[2];
        $ziely=$extras[3];

        $warp=12.67;

        $lichtjahre=sqrt(($kox-$zielx)*($kox-$zielx)+($koy-$ziely)*($koy-$ziely));
        $zeit=$lichtjahre/strecke($warp);

        if ($zeit<=1) {
            if ($extras[0]=='p') {
                $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set cantox=cantox+".$extras[4].", vorrat=vorrat+".$extras[5].", lemin=lemin+".$extras[6].", min1=min1+".$extras[7].", min2=min2+".$extras[8].", min3=min3+".$extras[9]." where id=".$extras[1]." and spiel=$spiel");
                $zeiger_temp = mysql_query("DELETE FROM $skrupel_anomalien  where id=$anoid and spiel=$spiel");

                $zeiger_temp = mysql_query("SELECT id,besitzer,name FROM $skrupel_planeten where spiel=$spiel and id=".$extras[1]);
                $array_temp = mysql_fetch_array($zeiger_temp);
                $name=$array_temp["name"];
                $besitzer=$array_temp["besitzer"];
                if ($besitzer>=1)  {
                    neuigkeiten(1,"../bilder/news/raumfalte.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['raumfalte'][0],array($name,$extras[4],$extras[6],$extras[8],$extras[5],$extras[7],$extras[9]));
                }
            }elseif ($extras[0]=='s') {
                $zeiger_temp = mysql_query("SELECT * FROM $skrupel_schiffe where id=".$extras[1]." and spiel=$spiel");
                $array_temp = mysql_fetch_array($zeiger_temp);
                $besitzer=$array_temp["besitzer"];
                $fracht_leute=$array_temp["fracht_leute"];
                $fracht_cantox=$array_temp["fracht_cantox"];
                $fracht_vorrat=$array_temp["fracht_vorrat"];
                $fracht_lemin=$array_temp["lemin"];
                $fracht_min1=$array_temp["fracht_min1"];
                $fracht_min2=$array_temp["fracht_min2"];
                $fracht_min3=$array_temp["fracht_min3"];
                $frachtraum=$array_temp["frachtraum"];
                $leminmax=$array_temp["leminmax"];
                $name=$array_temp["name"];

                $freiraum=$frachtraum-$fracht_min1-$fracht_min2-$fracht_min3-round($fracht_leute/100)-$fracht_vorrat;
                $freitank=$leminmax-$fracht_lemin;

                $p_min1=$extras[7];
                $p_min2=$extras[8];
                $p_min3=$extras[9];
                $p_vorrat=$extras[5];
                $p_cantox=$extras[4];
                $p_lemin=$extras[6];

                if ($p_min1<=$freiraum) { $freiraum=$freiraum-$p_min1;$fracht_min1=$fracht_min1+$p_min1; } else
                    {$fracht_min1=$fracht_min1+$freiraum;$freiraum=0; }
                if ($p_min2<=$freiraum) { $freiraum=$freiraum-$p_min2;$fracht_min2=$fracht_min2+$p_min2; } else
                    { $fracht_min2=$fracht_min2+$freiraum;$freiraum=0; }
                if ($p_min3<=$freiraum) { $freiraum=$freiraum-$p_min3;$fracht_min3=$fracht_min3+$p_min3; } else
                    { $fracht_min3=$fracht_min3+$freiraum;$freiraum=0; }

                if ($p_vorrat<=$freiraum) { $freiraum=$freiraum-$p_vorrat;$fracht_vorrat=$fracht_vorrat+$p_vorrat; } else
                    { $fracht_vorrat=$fracht_vorrat+$freiraum;$freiraum=0; }

                $fracht_cantox=$fracht_cantox+$p_cantox;

                if ($p_lemin<=$freitank){ $freitank=$freitank-$p_lemin;$fracht_lemin=$fracht_lemin+$p_lemin; } else
                                        { $fracht_lemin=$fracht_lemin+$freitank; }

                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set lemin=$fracht_lemin,fracht_vorrat=$fracht_vorrat,fracht_cantox=$fracht_cantox,fracht_min1=$fracht_min1,fracht_min2=$fracht_min2,fracht_min3=$fracht_min3 where id=".$extras[1]." and spiel=$spiel");
                $zeiger_temp = mysql_query("DELETE FROM $skrupel_anomalien  where id=$anoid and spiel=$spiel");

                neuigkeiten(2,"../bilder/news/raumfalte.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['raumfalte'][1],array($name,$extras[4],$extras[6],$extras[8],$extras[5],$extras[7],$extras[9]));
            }
        } else {
            $kox=round($kox+(($zielx-$kox)/$zeit));
            $koy=round($koy+(($ziely-$koy)/$zeit));
            $zeiger_temp = mysql_query("UPDATE $skrupel_anomalien set x_pos=$kox, y_pos=$koy where id=$anoid");
        }
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////RAUMFALTEN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////AUTOPROJEKTILE ANFANG

$zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where projektile_anzahl>=1 and projektile_auto=1 and spiel=$spiel");
$anoanzahl = mysql_num_rows($zeiger);

if ($anoanzahl>=1) {

    for  ($i=0; $i<$anoanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);

        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $projektile=$array["projektile"];
        $projektile_auto=$array["projektile_auto"];
        $projektile_stufe=$array["projektile_stufe"];
        $projektile_anzahl=$array["projektile_anzahl"];

        $fracht_cantox=$array["fracht_cantox"];
        $fracht_min1=$array["fracht_min1"];
        $fracht_min2=$array["fracht_min2"];

        $max=$projektile_anzahl*5;

        $max_bau=$max-$projektile;
        $max_cantox=floor($fracht_cantox/35);
        if ($max_cantox<$max_bau) {$max_bau=$max_cantox;}
        $max_min1=floor($fracht_min1/2);
        if ($max_min1<$max_bau) {$max_bau=$max_min1;}
        if ($fracht_min2<$max_bau) {$max_bau=$fracht_min2;}

        if ($max_bau>=1) {

            $projektile=$projektile+$max_bau;
            $fracht_cantox=$fracht_cantox-($max_bau*35);
            $fracht_min1=$fracht_min1-($max_bau*2);
            $fracht_min2=$fracht_min2-$max_bau;

            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set projektile=$projektile,fracht_cantox=$fracht_cantox,fracht_min1=$fracht_min1,fracht_min2=$fracht_min2 where id=$shid");
        }
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////AUTOPROJEKTILE ENDE
///////////////////////////////////////////////////////////////////////////////////////////////SCHIFFSBAU ANFANG
$neueschiffe=0;

$zeiger = mysql_query("SELECT * FROM $skrupel_sternenbasen where schiffbau_status=1 and status=1 and spiel=$spiel order by id");
$basenanzahl = mysql_num_rows($zeiger);
if ($basenanzahl>=1) {

    $neueschiffe=$neueschiffe+$basenanzahl;

    for  ($i=0; $i<$basenanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $baid=$array["id"];
        $x_pos=$array["x_pos"];
        $y_pos=$array["y_pos"];
        $zeiger2 = mysql_query("SELECT * FROM $skrupel_planeten where x_pos=$x_pos and y_pos=$y_pos and spiel=$spiel order by id");
        $ok = mysql_data_seek($zeiger2,0);
        $array2 = mysql_fetch_array($zeiger2);
        $osys_1=$array2["osys_1"];
        $osys_2=$array2["osys_2"];
        $osys_3=$array2["osys_3"];
        $osys_4=$array2["osys_4"];
        $osys_5=$array2["osys_5"];
        $osys_6=$array2["osys_6"];
        $besitzer=$array["besitzer"];
        $planetid=$array["planetid"];
        $schiffbau_klasse=$array["schiffbau_klasse"];
        $schiffbau_bild_gross=$array["schiffbau_bild_gross"];
        $schiffbau_bild_klein=$array["schiffbau_bild_klein"];
        $schiffbau_crew=$array["schiffbau_crew"];
        $schiffbau_masse=$array["schiffbau_masse"];
        $schiffbau_tank=$array["schiffbau_tank"];
        $schiffbau_fracht=$array["schiffbau_fracht"];
        $schiffbau_antriebe=$array["schiffbau_antriebe"];
        $schiffbau_energetik=$array["schiffbau_energetik"];
        $schiffbau_projektile=$array["schiffbau_projektile"];
        $schiffbau_hangar=$array["schiffbau_hangar"];
        $schiffbau_klasse_name=$array["schiffbau_klasse_name"];
        $schiffbau_rasse=$array["schiffbau_rasse"];
        $schiffbau_fertigkeiten=$array["schiffbau_fertigkeiten"];
        $schiffbau_energetik_stufe=$array["schiffbau_energetik_stufe"];
        $schiffbau_projektile_stufe=$array["schiffbau_projektile_stufe"];
        $schiffbau_techlevel=$array["schiffbau_techlevel"];
        $schiffbau_antriebe_stufe=$array["schiffbau_antriebe_stufe"];
        $schiffbau_name=$array["schiffbau_name"];
        $schiffbau_zusatz=$array["schiffbau_zusatz"];
        $schiffbau_extra=$array["schiffbau_extra"];
        $schalter=0;
        if(($osys_1==15) or ($osys_2==15) or ($osys_3==15) or ($osys_4==15) or ($osys_5==15) or ($osys_6==15)and $schiffbau_masse<100){
            $schalter=1;
            for($j=1;$j<=strlen($schiffbau_fertigkeiten);$j++){
                if(($j<53) or($j>55)){
                    if(intval(substr($schiffbau_fertigkeiten,$j,1))!=0){
                        $schalter=0;
                    }
                }
            }
        }
        if($schalter==1){
            if($schiffbau_energetik_stufe!=0){
                $vorrat_energetik_string='vorrat_energetik_'.$schiffbau_energetik_stufe;
            }else{
                $vorrat_energetik_string='vorrat_energetik_1';
            }
            if($schiffbau_projektile_stufe!=0){
                $vorrat_projektile_string='vorrat_projektile_'.$schiffbau_projektile_stufe;
            }else{
                $vorrat_projektile_string='vorrat_projektile_1';
            }
            if($schiffbau_antriebe_stufe!=0){
                $vorrat_antrieb_string='vorrat_antrieb_'.$schiffbau_antriebe_stufe;
            }else{
                $vorrat_antrieb_string='vorrat_antrieb_1';
            }
            $energetik=$array[$vorrat_energetik_string];
            $projektile=$array[$vorrat_projektile_string];
            $antrieb=$array[$vorrat_antrieb_string];
            if($energetik>=$schiffbau_energetik and $projektile>=$schiffbau_projektile and $antrieb>=$schiffbau_antriebe){
                $energetik=$energetik-$schiffbau_energetik;
                $projektile=$projektile-$schiffbau_projektile;
                $antrieb=$antrieb-$schiffbau_antriebe;
            }else{
                $schalter=0;
            }
            if($schalter==1){
                $zeiger3 = mysql_query("SELECT * FROM $skrupel_huellen where baid=$baid and spiel=$spiel and klasse=$schiffbau_klasse order by id");
                $huellenanzahl = mysql_num_rows($zeiger3);
                if($huellenanzahl>0){
                    $neueschiffe++;
                    $ok = mysql_data_seek($zeiger3,0);
                    $array3 = mysql_fetch_array($zeiger3);
                    $hid=$array3["id"];
                    $schiffbau_name2=$schiffbau_name.'(2)';
                    $zeiger_temp = mysql_query("INSERT INTO $skrupel_schiffe (s_x,s_y,besitzer,status,name,klasse,klasseid,volk,techlevel,antrieb,antrieb_anzahl,kox,koy,crew,crewmax,lemin,leminmax,frachtraum,masse,masse_gesamt,bild_gross,bild_klein,energetik_stufe,energetik_anzahl,projektile_stufe,projektile_anzahl,hanger_anzahl,schild,fertigkeiten,spiel,extra,zusatzmodul) values ($x_pos,$y_pos,$besitzer,2,'$schiffbau_name2','$schiffbau_klasse_name',$schiffbau_klasse,'$schiffbau_rasse',$schiffbau_techlevel,$schiffbau_antriebe_stufe, $schiffbau_antriebe,$x_pos,$y_pos,$schiffbau_crew,$schiffbau_crew,0,$schiffbau_tank,$schiffbau_fracht,$schiffbau_masse,$schiffbau_masse,'$schiffbau_bild_gross','$schiffbau_bild_klein',$schiffbau_energetik_stufe,$schiffbau_energetik,$schiffbau_projektile_stufe,$schiffbau_projektile,$schiffbau_hangar,100,'$schiffbau_fertigkeiten',$spiel,'$schiffbau_extra',$schiffbau_zusatz)");
                    $zeiger_temp = mysql_query("UPDATE $skrupel_sternenbasen set $vorrat_energetik_string='$energetik',$vorrat_projektile_string='$projektile',$vorrat_antrieb_string='$antrieb' where spiel=$spiel and id=$baid");
                    $zeiger_temp = mysql_query("DELETE FROM $skrupel_huellen where spiel=$spiel and id=$hid");
                    neuigkeiten(2,"../daten/$schiffbau_rasse/bilder_schiffe/$schiffbau_bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['schiffbau'][0],array($schiffbau_name2));
                    $schiffbau_name=$schiffbau_name.'(1)';
                }
            }
        }
        $zeiger_temp = mysql_query("INSERT INTO $skrupel_schiffe (s_x,s_y,besitzer,status,name,klasse,klasseid,volk,techlevel,antrieb,antrieb_anzahl,kox,koy,crew,crewmax,lemin,leminmax,frachtraum,masse,masse_gesamt,bild_gross,bild_klein,energetik_stufe,energetik_anzahl,projektile_stufe,projektile_anzahl,hanger_anzahl,schild,fertigkeiten,spiel,extra,zusatzmodul) values ($x_pos,$y_pos,$besitzer,2,'$schiffbau_name','$schiffbau_klasse_name',$schiffbau_klasse,'$schiffbau_rasse',$schiffbau_techlevel,$schiffbau_antriebe_stufe, $schiffbau_antriebe,$x_pos,$y_pos,$schiffbau_crew,$schiffbau_crew,0,$schiffbau_tank,$schiffbau_fracht,$schiffbau_masse,$schiffbau_masse,'$schiffbau_bild_gross','$schiffbau_bild_klein',$schiffbau_energetik_stufe,$schiffbau_energetik,$schiffbau_projektile_stufe,$schiffbau_projektile,$schiffbau_hangar,100,'$schiffbau_fertigkeiten',$spiel,'$schiffbau_extra',$schiffbau_zusatz)");
        neuigkeiten(2,"../daten/$schiffbau_rasse/bilder_schiffe/$schiffbau_bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['schiffbau'][0],array($schiffbau_name));
    }
}

$zeiger_temp = mysql_query("UPDATE $skrupel_sternenbasen set schiffbau_status=0,schiffbau_extra='' where spiel=$spiel");
mysql_query("UPDATE $skrupel_zugberechnen_daten SET neueschiffe=$neueschiffe WHERE sid='$sid'");
///////////////////////////////////////////////////////////////////////////////////////////////SCHIFFSBAU ENDE

///////////////////////////////////////////////////////////////////////////////////////////////GRAVITATION ANFANG

$zeiger2 = mysql_query("SELECT * FROM $skrupel_schiffe where status<>2 and spiel=$spiel order by id");
$schiffanzahl = mysql_num_rows($zeiger2);
if ($schiffanzahl>=1) {
    for  ($ir=0; $ir<$schiffanzahl;$ir++) {
        $ok2 = mysql_data_seek($zeiger2,$ir);
        $array2 = mysql_fetch_array($zeiger2);
        $shid=$array2["id"];
        $kox=$array2["kox"];
        $koy=$array2["koy"];
        $flug=$array2["flug"];
        $zielid=$array2["zielid"];
        $volk=$array2["volk"];
        $bild_gross=$array2["bild_gross"];
        $besitzer=$array2["besitzer"];
        $name=$array2["name"];

        $reichweite=13;

        $zeiger = mysql_query("SELECT * FROM $skrupel_planeten where (sqrt(((x_pos-$kox)*(x_pos-$kox))+((y_pos-$koy)*(y_pos-$koy)))<=$reichweite) and spiel=$spiel order by id");
        $planetenanzahl = mysql_num_rows($zeiger);
        if ($planetenanzahl>=1) {
            for  ($i=0; $i<$planetenanzahl;$i++) {
                $ok = mysql_data_seek($zeiger,$i);
                $array = mysql_fetch_array($zeiger);
                $pid=$array["id"];
                $x_pos=$array["x_pos"];
                $y_pos=$array["y_pos"];

                if ($pid==$zielid) {
                    neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['flug'][5],array($name));
                    $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set flug=0 where id=$shid");
                }
                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set kox=$x_pos,koy=$y_pos,status=2 where id=$shid");
            }
        }
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////GRAVITATION ENDE
///////////////////////////////////////////////////////////////////////////////////////////////MINENFELDER ANFANG
if($module[2]) {
    $zeiger = mysql_query("SELECT crew,crewmax,masse,id,name,klasse,klasseid,volk,kox,koy,besitzer,bild_gross,status,flug,schaden FROM $skrupel_schiffe where status=1 and not (klasseid=1 and volk like 'unknown') and spiel=$spiel order by id");
    $schiffanzahl = mysql_num_rows($zeiger);

    if ($schiffanzahl>=1) {

        for  ($i=0; $i<$schiffanzahl;$i++) {
            $ok = mysql_data_seek($zeiger,$i);

            $array = mysql_fetch_array($zeiger);
            $shid=$array["id"];
            $name=$array["name"];
            $klasse=$array["klasse"];
            $klasseid=$array["klasseid"];
            $kox=$array["kox"];
            $koy=$array["koy"];
            $volk=$array["volk"];
            $besitzer=$array["besitzer"];
            $bild_gross=$array["bild_gross"];
            $status=$array["status"];
            $leminmax=$array["leminmax"];
            $flug=$array["flug"];
            $schaden=$array["schaden"];
            $masse=$array["masse"];
            $crew=$array["crew"];
            $crewmax=$array["crewmax"];

            $reichweite=85;

            $minenanzahl=0;
            $zeiger2 = mysql_query("SELECT * FROM $skrupel_anomalien where spiel=$spiel and art=5 and (sqrt(((x_pos-$kox)*(x_pos-$kox))+((y_pos-$koy)*(y_pos-$koy)))<=$reichweite) order by id");
            //echo "$name $kox , $koy <br>";
            //echo "SELECT * FROM $skrupel_anomalien where spiel=$spiel and art=5 and x_pos>=$rand_x_a and x_pos<=$rand_x_b and y_pos>=$rand_y_a and y_pos<=$rand_y_b order by id"."<br><br>";
            $datensaetze2 = mysql_num_rows($zeiger2);
            if ($datensaetze2>=1) {
                for  ($irt=0; $irt<$datensaetze2;$irt++) {
                    $ok2 = mysql_data_seek($zeiger2,$irt);
                    $array2 = mysql_fetch_array($zeiger2);
                    $aid=$array2["id"];
                    $x_pos=$array2["x_pos"];
                    $y_pos=$array2["y_pos"];
                    $mineextra=explode(":",$array2["extra"]);

                    //echo $aid.':';
                    //$besitzerminenfeld=intval();
                    //echo $mineextra[1].'<br><br>';

                    if( ($mineextra[0]==$besitzer) or
                        ($beziehung[$besitzer][$mineextra[0]]['status']==3) or
                        ($beziehung[$besitzer][$mineextra[0]]['status']==4) or
                        ($beziehung[$besitzer][$mineextra[0]]['status']==5))
                    {} else {

                        if (intval($mineextra[1])>=$minenanzahl) {
                            $minenanzahl=intval($mineextra[1]);

                            $aanomalie[0]=$aid; // id
                            $aanomalie[1]=$mineextra[0]; // besitzer
                            $aanomalie[2]=intval($mineextra[1]); // anzahl
                            $aanomalie[3]=$mineextra[2]; // stufe
                        }
                    }
                }
            }

            //echo $minenanzahl.'<br>';

            if ($minenanzahl>=1) {
                $zufall=mt_rand(0,50);
                $minentreffer=round($zufall*$minenanzahl/100);
                //echo $minentreffer.':';
                if ($minenanzahl==1) { $minentreffer=1; }
                if ($minentreffer>=1) {

                    $aanomalie[2]=$aanomalie[2]-$minentreffer;
                    if ($aanomalie[2]<=0) {
                        $zeiger_temp = mysql_query("DELETE FROM $skrupel_anomalien where spiel=$spiel and id=$aanomalie[0]");
                    } else {
                        $mineextra=$aanomalie[1].':'.$aanomalie[2].':'.$aanomalie[3];
                        $zeiger_temp = mysql_query("UPDATE $skrupel_anomalien set extra='$mineextra' where spiel=$spiel and id=$aanomalie[0]");
                    }

                    $minen_schaden=$torpedoschaden["$aanomalie[3]"];
                    $minen_schaden_crew=$torpedoschadencrew["$aanomalie[3]"];

                    //echo $minen_schaden.':'.$masse.':'.$minentreffer.'<br><br>';
                    $schaden_rumpf=round(($minen_schaden*(80/($masse+1))*(80/($masse+1))+2))*$minentreffer;
                    $schaden=$schaden+$schaden_rumpf;
                    $schaden_crew=($minen_schaden_crew*(80/($masse+1))*(80/($masse+1))+2)*$minentreffer;
                    $crew=$crew-floor($crewmax*$schaden_crew/100);
                    $schaden_crewmen=floor($crewmax*$schaden_crew/100);

                    $sektork=sektor($kox,$koy);

                    //echo $schaden_rumpf.":";
                    //echo $schaden_crewmen."<br><br>";

                    //echo $schaden.":".$crew;

                    if (($schaden>=100) or ($crew<1)) {

                        $zeiger_temp = mysql_query("DELETE FROM $skrupel_schiffe where id=$shid and besitzer=$besitzer;");
                        $zeiger_temp = mysql_query("DELETE FROM $skrupel_anomalien where art=3 and extra like 's:$shid:%'");
                        $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where (flug=3 or flug=4) and zielid=$shid");

                        neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['minenfelder'][0],array($name,$sektork));
                    } else {
                        $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set crew=$crew,schaden=$schaden,scanner=0 where id=$shid and besitzer=$besitzer;");

                        neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['minenfelder'][1],array($name,$sektork,$minentreffer,$schaden_rumpf,$schaden_crewmen));
                    }
                }
            }
        }
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////MINENFELDER ENDE
///////////////////////////////////////////////////////////////////////////////////////////////MINENFELDER SCHRUMPFEN ANFANG
if($module[2]) {

    $zeiger2 = mysql_query("SELECT id,extra,spiel FROM $skrupel_anomalien where spiel=$spiel and art=5 order by id");
    $datensaetze2 = mysql_num_rows($zeiger2);
    if ($datensaetze2>=1) {
        for ($irt=0; $irt<$datensaetze2;$irt++) {
            $ok2 = mysql_data_seek($zeiger2,$irt);
            $array2 = mysql_fetch_array($zeiger2);
            $aid=$array2["id"];
            $mineextra=explode(":",$array2["extra"]);

            $aanomalie[0]=$aid; // id
            $aanomalie[1]=$mineextra[0]; // besitzer
            $aanomalie[2]=intval($mineextra[1]); // anzahl
            $aanomalie[3]=$mineextra[2]; // stufe

            $zufall=mt_rand(0,100);
            if ($zufall<=80) {

                $aanomalie[2]=$aanomalie[2]-1;

                if ($aanomalie[2]<=0) {
                    $zeiger_temp = mysql_query("DELETE FROM $skrupel_anomalien where spiel=$spiel and id=$aanomalie[0]");
                } else {
                    $mineextra=$aanomalie[1].':'.$aanomalie[2].':'.$aanomalie[3];
                    $zeiger_temp = mysql_query("UPDATE $skrupel_anomalien set extra='$mineextra' where spiel=$spiel and id=$aanomalie[0]");
                }
            }
        }
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////MINENFELDER SCHRUMPFEN ENDE

$schiffevernichtet=0; // benutzt in inc.host_spionage.php, inc.host_orbitalkampf.php und inc.host_raumkampf.php

///////////////////////////////////////////////////////////////////////////////////////////////SPIONAGE ANFANG
if($module[0]) {
    include(INCLUDEDIR.'inc.host_spionage.php');
}
///////////////////////////////////////////////////////////////////////////////////////////////SPIONAGE ENDE


///////////////////////////////////////////////////////////////////////////////////////////////SUBRAUMVERZERRUNG BETA ANFANG
$zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where spezialmission=10 and spiel=$spiel order by id");
$schiffanzahl = mysql_num_rows($zeiger);
if ($schiffanzahl>=1) {
    for ($i=0; $i<$schiffanzahl;$i++) {
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

        $zeiger_temp = mysql_query("SELECT * FROM $skrupel_schiffe where (sqrt(($kox-kox)*($kox-kox)+($koy-koy)*($koy-koy))<=83) and spezialmission<>10 and spiel=$spiel order by id");
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
///////////////////////////////////////////////////////////////////////////////////////////////SUBRAUMVERZERRUNG BETA ENDE

///////////////////////////////////////////////////////////////////////////////////////////////SCHIFFSKAMPF PLANET ANFANG

include(INCLUDEDIR.'inc.host_orbitalkampf.php');

///////////////////////////////////////////////////////////////////////////////////////////////SCHIFFSKAMPF PLANET ENDE

///////////////////////////////////////////////////////////////////////////////////////////////SCHIFFSKAMPF ANFANG

include(INCLUDEDIR.'inc.host_raumkampf.php');

///////////////////////////////////////////////////////////////////////////////////////////////SCHIFFSKAMPF ENDE

///////////////////////////////////////////////////////////////////////////////////////////////STERNENBASEN ANFANG

///////////////////////////////////////////////////////////////////////////////////////////////STERNENBASEN BAUEN ANFANG
$neuebasen=0;

$zeiger = mysql_query("SELECT * FROM $skrupel_sternenbasen where status=0 and spiel=$spiel order by id");
$basenanzahl = mysql_num_rows($zeiger);

if ($basenanzahl>=1) {


    for ($i=0; $i<$basenanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);

        $array = mysql_fetch_array($zeiger);
        $bid=$array["id"];
        $name=$array["name"];
        $rasse=$array["rasse"];
        $planetid=$array["planetid"];
        $besitzer=$array["besitzer"];

        $zeiger_temp = mysql_query("UPDATE $skrupel_sternenbasen set status=1 where id=$bid");
        $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set sternenbasis=2 where id=$planetid");

        $neuebasen++;
        neuigkeiten(3,"../daten/$rasse/bilder_basen/1.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['basenbauen'][0],array($name));
    }
}
mysql_query("UPDATE $skrupel_zugberechnen_daten set neuebasen=$neuebasen WHERE sid='$sid'");

///////////////////////////////////////////////////////////////////////////////////////////////STERNENBASEN BAUEN ENDE

///////////////////////////////////////////////////////////////////////////////////////////////STERNENBASEN ENDE

///////////////////////////////////////////////////////////////////////////////////////////////SCHIFF WEICHT PLANET AUS ANFANG

$zeiger = mysql_query("SELECT id,status,besitzer,name,klasse,klasseid,kox,koy,volk,bild_gross FROM $skrupel_schiffe where status=2 and spiel=$spiel order by id");
$schiffanzahl = mysql_num_rows($zeiger);

if ($schiffanzahl>=1) {

    for ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);

        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $status=$array["status"];
        $besitzer=$array["besitzer"];
        $name=$array["name"];
        $klasse=$array["klasse"];
        $klasseid=$array["klasseid"];
        $kox=$array["kox"];
        $koy=$array["koy"];
        $volk=$array["volk"];
        $bild_gross=$array["bild_gross"];

        $gemeinsam=0;
        $zeiger_temp = mysql_query("SELECT count(*) as gemeinsam FROM $skrupel_planeten where x_pos=$kox and y_pos=$koy and besitzer<>$besitzer and besitzer>=1 and spiel=$spiel");
        $array_temp = mysql_fetch_array($zeiger_temp);
        $gemeinsam=$array_temp["gemeinsam"];

        if ($gemeinsam>=1) {

            $zeiger2 = mysql_query("SELECT x_pos,y_pos,spiel,id,name,besitzer,bild,klasse FROM $skrupel_planeten where x_pos=$kox and y_pos=$koy and spiel=$spiel");
            $array2 = mysql_fetch_array($zeiger2);
            $p_id=$array2["id"];
            $p_name=$array2["name"];
            $p_besitzer=$array2["besitzer"];
            $p_bild=$array2["bild"];
            $p_klasse=$array2["klasse"];

            if (($beziehung[$besitzer][$p_besitzer]['status']==3) or ($beziehung[$besitzer][$p_besitzer]['status']==4)) {

                $alpha=(double)(6.28318530718*mt_rand(0,$mt_randmax)/$mt_randmax);
                $koy=max(0,min($umfang,$koy+round(20*sin($alpha))));
                $kox=max(0,min($umfang,$kox+round(20*cos($alpha))));

                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set status=1,kox=$kox, koy=$koy where id=$shid and spiel=$spiel");
                neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['ausweichen'][0],array($name,$spielerfarbe[$p_besitzer],$p_name));
            }
        }
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////SCHIFF WEICHT PLANET AUS ENDE
///////////////////////////////////////////////////////////////////////////////////////////////SCHIFF WEICHT SCHIFF AUS ANFANG

$zeiger = mysql_query("SELECT id,status,masse,besitzer,name,klasse,klasseid,kox,koy,volk,bild_gross FROM $skrupel_schiffe where spiel=$spiel order by id");
$schiffanzahl = mysql_num_rows($zeiger);

$checkstring="";

if ($schiffanzahl>=1) {

    for ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);

        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $status=$array["status"];
        $besitzer=$array["besitzer"];
        $name=$array["name"];
        $klasse=$array["klasse"];
        $klasseid=$array["klasseid"];
        $kox=$array["kox"];
        $koy=$array["koy"];
        $masse=$array["masse"];
        $volk=$array["volk"];
        $bild_gross=$array["bild_gross"];


        $code=":::".$shid.":::";
        if (strstr($checkstring,$code)) {} else {

            $gemeinsam=0;
            $zeiger_temp = mysql_query("SELECT count(*) as gemeinsam FROM $skrupel_schiffe where kox=$kox and koy=$koy and besitzer<>$besitzer and spiel=$spiel");
            $array_temp = mysql_fetch_array($zeiger_temp);
            $gemeinsam=$array_temp["gemeinsam"];

            if ($gemeinsam>=1) {

                $zeiger2 = mysql_query("SELECT id,status,masse,besitzer,name,klasse,klasseid,kox,koy,volk,bild_gross FROM $skrupel_schiffe where kox=$kox and koy=$koy and id<>$shid and besitzer<>$besitzer and spiel=$spiel");

                for ($ihj=0; $ihj<$gemeinsam;$ihj++) {
                    $ok2 = mysql_data_seek($zeiger2,$ihj);

                    $code=":::".$shid.":::";
                    if (strstr($checkstring,$code)) {} else {

                        $array2 = mysql_fetch_array($zeiger2);
                        $shid_2=$array2["id"];
                        $status_2=$array2["status"];
                        $besitzer_2=$array2["besitzer"];
                        $name_2=$array2["name"];
                        $klasse_2=$array2["klasse"];
                        $klasseid_2=$array2["klasseid"];
                        $kox_2=$array2["kox"];
                        $koy_2=$array2["koy"];
                        $masse_2=$array2["masse"];
                        $volk_2=$array2["volk"];
                        $bild_gross_2=$array2["bild_gross"];

                        if ($status==2) {
                            $abstand=20;

                            $zeiger3 = mysql_query("SELECT x_pos,y_pos,spiel,id,besitzer FROM $skrupel_planeten where x_pos=$kox and y_pos=$koy and spiel=$spiel");
                            $array3 = mysql_fetch_array($zeiger3);
                            $p_besitzer=$array3["besitzer"];

                            if ($p_besitzer==$besitzer) { $springer=2; }
                            if ($p_besitzer==$besitzer_2) { $springer=1; }
                            if (($p_besitzer!=$besitzer) and ($p_besitzer!=$besitzer_2)) {
                                if ($masse==$masse_2) {
                                    $springer=mt_rand(1,2);
                                } else {
                                    if ($masse>$masse_2) {
                                        $springer=2;
                                    } else {
                                        $springer=1;
                                    }
                                }
                            }
                        } else {
                            $abstand=15;
                            if ($masse==$masse_2) {
                                $springer=mt_rand(1,2);
                            } else {
                                if ($masse>$masse_2) {
                                    $springer=2;
                                } else {
                                    $springer=1;
                                }
                            }
                        }

                        $alpha=(double)(6.28318530718*mt_rand(0,$mt_randmax)/$mt_randmax);
                        $koy=max(0,min($umfang,$koy+round(20*sin($alpha))));
                        $kox=max(0,min($umfang,$kox+round(20*cos($alpha))));

                        if ($springer==1) {

                            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set status=1,kox=$kox, koy=$koy where id=$shid and spiel=$spiel");
                            neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['ausweichen'][1],array($name,$spielerfarbe[$besitzer_2],$name_2));
                            $checkstring=$checkstring.":::".$shid.":::";
                        } else {
                            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set status=1,kox=$kox, koy=$koy where id=$shid_2 and spiel=$spiel");
                            neuigkeiten(2,"../daten/$volk_2/bilder_schiffe/$bild_gross_2",$besitzer_2,$lang['host'][$spielersprache[$besitzer_2]]['ausweichen'][1],array($name_2,$spielerfarbe[$besitzer],$name));
                            $checkstring=$checkstring.":::".$shid_2.":::";
                        }
                    }
                }
            }
        }
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////SCHIFF WEICHT SCHIFF AUS ENDE
/////////////////////////////////////////////////////////////////////////////////////////////SPEZIALMISSIONEN ANFANG

$zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where spezialmission>=1 and spiel=$spiel order by id");
$schiffanzahl = mysql_num_rows($zeiger);

if ($schiffanzahl>=1) {

    for ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);

        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $name=$array["name"];
        $masse=$array["masse"];
        $klasse=$array["klasse"];
        $antrieb=$array["antrieb"];
        $klasseid=$array["klasseid"];
        $kox=$array["kox"];
        $koy=$array["koy"];
        $volk=$array["volk"];
        $besitzer=$array["besitzer"];
        $bild_gross=$array["bild_gross"];
        $frachtraum=$array["frachtraum"];
        $lemin=$array["lemin"];
        $leminmax=$array["leminmax"];
        $crew=$array["crew"];
        $crewmax=$array["crewmax"];
        $flug=$array["flug"];
        $schaden=$array["schaden"];
        $leichtebt=$array["leichtebt"];
        $schwerebt=$array["schwerebt"];
        $erfahrung=$array["erfahrung"];
        $energetik_stufe=$array["energetik_stufe"];
        $energetik_anzahl=$array["energetik_anzahl"];
        $projektile_stufe=$array["projektile_stufe"];
        $projektile_anzahl=$array["projektile_anzahl"];
        $projektile=$array["projektile"];
        $hanger_anzahl=$array["hanger_anzahl"];
        $sprungtorbauid=$array["sprungtorbauid"];
        $fertigkeiten=$array["fertigkeiten"];
        $spezialmission=$array["spezialmission"];
        $status=$array["status"];

        $extra = explode(":", trim($array['extra']));

        $fracht_leute=$array["fracht_leute"];
        $fracht_cantox=$array["fracht_cantox"];
        $fracht_vorrat=$array["fracht_vorrat"];
        $fracht_lemin=$array["lemin"];
        $fracht_min1=$array["fracht_min1"];
        $fracht_min2=$array["fracht_min2"];
        $fracht_min3=$array["fracht_min3"];
        $zusatzmodul=$array["zusatzmodul"];

        $frachtfrei=$frachtraum-$fracht_vorrat-$fracht_min1-$fracht_min2-$fracht_min3-floor($fracht_leute/100);
        $tankfrei=$leminmax-$fracht_lemin;

        $fert_sub_vorrat=intval(substr($fertigkeiten,0,2));
        $fert_sub_min1=intval(substr($fertigkeiten,2,1));
        $fert_sub_min2=intval(substr($fertigkeiten,3,1));
        $fert_sub_min3=intval(substr($fertigkeiten,4,1));

        $fert_terra_warm=intval(substr($fertigkeiten,5,1));
        $fert_terra_kalt=intval(substr($fertigkeiten,6,1));

        $fert_quark_vorrat=intval(substr($fertigkeiten,7,1));
        $fert_quark_min1=intval(substr($fertigkeiten,8,1));
        $fert_quark_min2=intval(substr($fertigkeiten,9,1));
        $fert_quark_min3=intval(substr($fertigkeiten,10,1));

        $fert_sprung_kosten=intval(substr($fertigkeiten,11,3));
        $fert_sprung_min=intval(substr($fertigkeiten,14,4));
        $fert_sprung_max=intval(substr($fertigkeiten,18,4));

        $fert_sprungtorbau_min1=intval(substr($fertigkeiten,25,3));
        $fert_sprungtorbau_min2=intval(substr($fertigkeiten,28,3));
        $fert_sprungtorbau_min3=intval(substr($fertigkeiten,31,3));
        $fert_sprungtorbau_lemin=intval(substr($fertigkeiten,34,3));

        $fert_reperatur=intval(substr($fertigkeiten,37,1));

        $viralmin=intval(substr($fertigkeiten,41,2));
        $viralmax=intval(substr($fertigkeiten,43,3));

        $erwtrans=intval(substr($fertigkeiten,46,2));
        $cybern=intval(substr($fertigkeiten,48,2));
        $destabi=intval(substr($fertigkeiten,50,2));

        /////////////////////////////////////////////////////////////////////////////////////////////MINENFELD RAEUMEN ANFANG
        if (($module[2]) and ($spezialmission==25) and ($hanger_anzahl>=1)) {
            if($status!=2){
                $erfolg=0;
                if ($hanger_anzahl==1) { $erfolg=1; }
                if ($hanger_anzahl>=2) {
                    $erfolg=mt_rand(round($hanger_anzahl/2),$hanger_anzahl);
                }
                //echo $erfolg;
                if ($erfolg>=1) {

                    $reichweite=100;

                    $minenanzahl=0;
                    $zeiger2 = mysql_query("SELECT * FROM $skrupel_anomalien where spiel=$spiel and art=5 and (sqrt(((x_pos-$kox)*(x_pos-$kox))+((y_pos-$koy)*(y_pos-$koy)))<=$reichweite) order by id");
                    $datensaetze2 = mysql_num_rows($zeiger2);

                    if ($datensaetze2>=1) {
                        for ($irt=0; $irt<$datensaetze2;$irt++) {
                            $ok2 = mysql_data_seek($zeiger2,$irt);
                            $array2 = mysql_fetch_array($zeiger2);
                            $aid=$array2["id"];
                            $x_pos=$array2["x_pos"];
                            $y_pos=$array2["y_pos"];
                            $mineextra=explode(":",$array2["extra"]);

                            if(    ($mineextra[0]==$besitzer) or
                                ($beziehung[$besitzer][$mineextra[0]]['status']==3) or
                                ($beziehung[$besitzer][$mineextra[0]]['status']==4) or
                                ($beziehung[$besitzer][$mineextra[0]]['status']==5))
                            {} else {

                                if (intval($mineextra[1])>=$minenanzahl) {
                                    $minenanzahl=intval($mineextra[1]);

                                    $aanomalie[0]=$aid; // id
                                    $aanomalie[1]=$mineextra[0]; // besitzer
                                    $aanomalie[2]=intval($mineextra[1]); // anzahl
                                    $aanomalie[3]=$mineextra[2]; // stufe
                                }
                            }
                        }
                    }

                    if ($minenanzahl>=1) {
                        $aanomalie[2]=$aanomalie[2]-$erfolg;
                        if ($aanomalie[2]<=0) {

                            $zeiger_temp = mysql_query("DELETE FROM $skrupel_anomalien where spiel=$spiel and id=$aanomalie[0]");

                            neuigkeiten(4,"../bilder/news/minenfeld.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['minenfelder'][2],array($name));
                            neuigkeiten(4,"../bilder/news/minenfeld.jpg",$aanomalie[1],$lang['host'][$spielersprache[$aanomalie[1]]]['minenfelder'][3]);
                        } else {
                            neuigkeiten(4,"../bilder/news/minenfeld.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['minenfelder'][4],array($name,$erfolg));
                            $mineextra=$aanomalie[1].':'.$aanomalie[2].':'.$aanomalie[3];
                            $zeiger_temp = mysql_query("UPDATE $skrupel_anomalien set extra='$mineextra' where spiel=$spiel and id=$aanomalie[0]");
                        }
                    }
                }
            }else{
                neuigkeiten(4,"../bilder/news/minenfeld.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['minenfelder'][7],array($name));
                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set spezialmission=0 where id=$shid");
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////MINENFELD RAEUMEN ENDE
        /////////////////////////////////////////////////////////////////////////////////////////////MINENFELD LEGEN ANFANG
        if (($module[2]) and ($spezialmission==24)) {
            if($status!=2){
                $legen=intval($extra[2]);
                if ($legen>$projektile) {$legen=$projektile;}
                if ($legen>=1) {
                    $projektile=$projektile-$legen;
                    $extra[2]=0;
                    $extra_neu = implode(":", $extra);
                    $mineextra=$besitzer.':'.$legen.':'.$projektile_stufe;

                    $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set projektile=$projektile,spezialmission=0,extra='$extra_neu' where id=$shid");
                    $zeiger_temp = mysql_query("INSERT INTO $skrupel_anomalien (art,x_pos,y_pos,extra,spiel) values (5,$kox,$koy,'$mineextra',$spiel);");
                    neuigkeiten(4,"../bilder/news/minenfeld.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['minenfelder'][5],array($name,$legen));
                } else {
                    $extra[2]=0;
                    $extra_neu = implode(":", $extra);
                    $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set spezialmission=0,extra='$extra_neu' where id=$shid");
                }
            }else{
                neuigkeiten(4,"../bilder/news/minenfeld.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['minenfelder'][6],array($name));
                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set spezialmission=0 where id=$shid");
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////MINENFELD LEGEN ENDE
        /////////////////////////////////////////////////////////////////////////////////////////////AUTOGRAPSCH ANFANG

        unset($a_planet);


        // Fuer alle Eventualitaeten:
        // Hole Datensatz des Planeten
        if(    (    ($spezialmission==26) or
                ($spezialmission==27) or
                ($spezialmission==28)
            ) and ($status==2))
        {
            // Hole Planeten, um den das aktuelle Schiff gerade kreist
            $query_ret=mysql_query( "SELECT * FROM $skrupel_planeten WHERE x_pos=$kox and y_pos=$koy and spiel=$spiel;");
            // Mache nur was, wenn da auch nur ein Planet ist
            if($query_ret && (mysql_num_rows($query_ret)==1) )
            {
                $a_planet=mysql_fetch_array($query_ret);
                $planet_id=$a_planet["id"];
            }
        }

        // bei Quarkern gehen wir vorsichtiger vor:
        // 113 Lemin runter, 113 Vorraete und 113 Material rauf
        if( ($spezialmission==26) && ($status==2) && $a_planet)
        {
            beam_s_p($conn, $shid,$planet_id,"lemin",113);

            $p_vorrat=$a_planet["vorrat"];
                $p_min1=$a_planet["min1"];
                $p_min2=$a_planet["min2"];
                $p_min3=$a_planet["min3"];
                $osys_1=$a_planet["osys_1"];
                $osys_2=$a_planet["osys_2"];
                $osys_3=$a_planet["osys_3"];
                $osys_4=$a_planet["osys_4"];
                $osys_5=$a_planet["osys_5"];
                $osys_6=$a_planet["osys_6"];
                $p_besitzer=$a_planet["besitzer"];
            if((($osys_1==7) or ($osys_2==7) or ($osys_3==7) or ($osys_4==7) or ($osys_5==7)or ($osys_6==7))and($p_besitzer!=$besitzer)){
                $p_min1=max(0,($p_min1-100));
                $p_min2=max(0,($p_min2-100));
                $p_min3=max(0,($p_min3-100));
            }
            $uber=113;
            if ($fert_quark_vorrat>=1){
                $uber=min(113,$uber,floor(($p_vorrat+$fracht_vorrat)/$fert_quark_vorrat));
            }
            if ($fert_quark_min1>=1){
                $uber=min(113,$uber,floor(($p_min1+$fracht_min1)/$fert_quark_min1));
            }
            if ($fert_quark_min2>=1){
                $uber=min(113,$uber,floor(($p_min2+$fracht_min2)/$fert_quark_min2));
            }
            if ($fert_quark_min3>=1){
                $uber=min(113,$uber,floor(($p_min3+$fracht_min3)/$fert_quark_min3));
            }
            if($fert_quark_vorrat>=1){
                $fert_quark_vorrat_t=($fert_quark_vorrat*$uber)-$fracht_vorrat;
                $fert_quark_vorrat_t=max(0,$fert_quark_vorrat_t);
                $fracht_vorrat+=beam_p_s($conn, $planet_id, $shid, "vorrat", $fert_quark_vorrat_t);
            }
            if ($fert_quark_min1>=1){
                $fert_quark_min1_t=($fert_quark_min1*$uber)-$fracht_min1;
                $fert_quark_min1_t=max(0,$fert_quark_min1_t);
                $fracht_min1+=beam_p_s($conn, $planet_id, $shid, "min1", $fert_quark_min1_t);
            }
            if ($fert_quark_min2>=1){
                $fert_quark_min2_t=($fert_quark_min2*$uber)-$fracht_min2;
                $fert_quark_min2_t=max(0,$fert_quark_min2_t);
                $fracht_min2+=beam_p_s($conn, $planet_id, $shid, "min2", $fert_quark_min2_t);
            }
            if ($fert_quark_min3>=1){
                $fert_quark_min3_t=($fert_quark_min3*$uber)-$fracht_min3;
                $fert_quark_min3_t=max(0,$fert_quark_min3_t);
                $fracht_min3+=beam_p_s($conn, $planet_id, $shid, "min3", $fert_quark_min3_t);
            }
        }

        // Wenn Subpartikelcluster an ist und das Schiff sich im Planetenorbit
        // befindet: alles abladen und Vorraete fassen
        if( ($spezialmission==27) && ($status==2) && $a_planet){
            $p_vorrat=$a_planet["vorrat"];
            $osys_1=$a_planet["osys_1"];
            $osys_2=$a_planet["osys_2"];
            $osys_3=$a_planet["osys_3"];
            $osys_4=$a_planet["osys_4"];
            $osys_5=$a_planet["osys_5"];
            $osys_6=$a_planet["osys_6"];
            $p_besitzer=$a_planet["besitzer"];
            if((($osys_1==7) or ($osys_2==7) or ($osys_3==7) or ($osys_4==7) or ($osys_5==7)or ($osys_6==7))and($p_besitzer!=$besitzer)){
                $p_vorrat=max(0,($p_vorrat-100));
            }
            $fert_sub_vorrat_t=min($fert_sub_vorrat*287,floor(($p_vorrat+$fracht_vorrat)/$fert_sub_vorrat)*$fert_sub_vorrat);

            // Erstmal alles runterbeamen
            beam_s_p($conn, $shid,$planet_id,"vorrat",$frachtraum);
            beam_s_p($conn, $shid,$planet_id,"min1",$frachtraum);
            beam_s_p($conn, $shid,$planet_id,"min2",$frachtraum);
            beam_s_p($conn, $shid,$planet_id,"min3",$frachtraum);

            // Dann ordentlich Vorraete rauf beamen
            $fracht_vorrat=beam_p_s($conn, $planet_id, $shid, "vorrat", $fert_sub_vorrat_t);
        }

        // Wenn Cybernrittnikk an ist und das Schiff sich im Planetenorbit
        // befindet: Kolos abladen und Vorraete fassen
        if( ($spezialmission==28) && ($status==2) && $a_planet){
            // Erstmal alles runterbeamen
            // Aber nur, wenn der Planet niemand wichtigem gehoert.

            if($beziehung[$a_planet["besitzer"]][$besitzer]['status']<3){
                beam_s_p($conn, $shid,$planet_id,"kolonisten",$frachtraum*100);
            }
            $p_vorrat=$a_planet["vorrat"];
            $osys_1=$a_planet["osys_1"];
            $osys_2=$a_planet["osys_2"];
            $osys_3=$a_planet["osys_3"];
            $osys_4=$a_planet["osys_4"];
            $osys_5=$a_planet["osys_5"];
            $osys_6=$a_planet["osys_6"];
            $p_besitzer=$a_planet["besitzer"];
            if((($osys_1==7) or ($osys_2==7) or ($osys_3==7) or ($osys_4==7) or ($osys_5==7)or ($osys_6==7))and($p_besitzer!=$besitzer)){
                $p_vorrat=max(0,($p_vorrat-100));
            }
            $p_vorrat=min(220,$p_vorrat);
            // Dann ordentlich Vorraete rauf beamen
            $fracht_vorrat+=beam_p_s($conn, $planet_id, $shid, "vorrat", $p_vorrat);
        }

        /////////////////////////////////////////////////////////////////////////////////////////////AUTOGRAPSCH ENDE
        /////////////////////////////////////////////////////////////////////////////////////////////SUBPARTIKELVERZERRUNG ANFANG
        if (    ($fracht_vorrat>=$fert_sub_vorrat)
                and ($fert_sub_vorrat>=1)
                and ( ($spezialmission==4) or ($spezialmission==27) ) )
        {
            $max=floor($fracht_vorrat/$fert_sub_vorrat);
            //$maxraum=floor($frachtfrei/($fert_sub_min1+$fert_sub_min2+$fert_sub_min3));
            //if ($maxraum < $max) { $max=$maxraum; }

            if (287<$max) {$max=287;}
            if ($max>=1) {

                $vorrat_verbrauch=$max*$fert_sub_vorrat;
                $min1_prod=$max*$fert_sub_min1;
                $min2_prod=$max*$fert_sub_min2;
                $min3_prod=$max*$fert_sub_min3;

                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set fracht_vorrat=fracht_vorrat-$vorrat_verbrauch,fracht_min1=fracht_min1+$min1_prod,fracht_min2=fracht_min2+$min2_prod,fracht_min3=fracht_min3+$min3_prod where id=$shid");
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////SUBPARTIKELVERZERRUNG ENDE
        /////////////////////////////////////////////////////////////////////////////////////////////DESTABILISATOR ANFANG
        if (($spezialmission==20)and ($status==2)) {
            $zufall=mt_rand(1,100);
            if ($zufall<=$destabi) {
                $zeiger2 = mysql_query("SELECT * FROM $skrupel_planeten where x_pos=$kox and y_pos=$koy and besitzer<>$besitzer and spiel=$spiel");
                $planetenanzahl = mysql_num_rows($zeiger2);

                if ($planetenanzahl==1) {
                    $array2 = mysql_fetch_array($zeiger2);
                    $p_id=$array2["id"];
                    $p_besitzer=$array2["besitzer"];
                    $p_name=$array2["name"];
                    $osys_1=$array2["osys_1"];
                    $osys_2=$array2["osys_2"];
                    $osys_3=$array2["osys_3"];
                    $osys_4=$array2["osys_4"];
                    $osys_5=$array2["osys_5"];
                    $osys_6=$array2["osys_6"];
                    if(($osys_1!=19) and ($osys_2!=19) and ($osys_3!=19) and ($osys_4!=19) and ($osys_5!=19) and ($osys_6!=19)){
                        if ($beziehung[$besitzer][$p_besitzer]['status']!=5) {
                            $sektork=sektor($kox,$koy);
                            $zeiger_temp = mysql_query("DELETE FROM $skrupel_planeten where id=$p_id and besitzer=$p_besitzer;");
                            $zeiger_temp = mysql_query("DELETE FROM $skrupel_anomalien where art=3 and extra like 'p:$p_id:%'");
                            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where flug=2 and zielid=$p_id");
                            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set status=1 where kox=$kox and koy=$koy and spiel=$spiel");
                            $zeiger_temp = mysql_query("DELETE FROM $skrupel_sternenbasen where x_pos=$kox and y_pos=$koy and spiel=$spiel");

                            $suche=array('{1}','{2}');
                            $ersetzen=array($p_name,$sektork);
                            $text=str_replace($suche,$ersetzen,$text);

                            if (($spieler_1>=1) and ($p_besitzer<>1)) { neuigkeiten(4,"../bilder/news/star_explode.jpg",1,$lang['host'][$spielersprache[1]]['destabilisator'][0],array($p_name,$sektork)); }
                            if (($spieler_2>=1) and ($p_besitzer<>2)) { neuigkeiten(4,"../bilder/news/star_explode.jpg",2,$lang['host'][$spielersprache[2]]['destabilisator'][0],array($p_name,$sektork)); }
                            if (($spieler_3>=1) and ($p_besitzer<>3)) { neuigkeiten(4,"../bilder/news/star_explode.jpg",3,$lang['host'][$spielersprache[3]]['destabilisator'][0],array($p_name,$sektork)); }
                            if (($spieler_4>=1) and ($p_besitzer<>4)) { neuigkeiten(4,"../bilder/news/star_explode.jpg",4,$lang['host'][$spielersprache[4]]['destabilisator'][0],array($p_name,$sektork)); }
                            if (($spieler_5>=1) and ($p_besitzer<>5)) { neuigkeiten(4,"../bilder/news/star_explode.jpg",5,$lang['host'][$spielersprache[5]]['destabilisator'][0],array($p_name,$sektork)); }
                            if (($spieler_6>=1) and ($p_besitzer<>6)) { neuigkeiten(4,"../bilder/news/star_explode.jpg",6,$lang['host'][$spielersprache[6]]['destabilisator'][0],array($p_name,$sektork)); }
                            if (($spieler_7>=1) and ($p_besitzer<>7)) { neuigkeiten(4,"../bilder/news/star_explode.jpg",7,$lang['host'][$spielersprache[7]]['destabilisator'][0],array($p_name,$sektork)); }
                            if (($spieler_8>=1) and ($p_besitzer<>8)) { neuigkeiten(4,"../bilder/news/star_explode.jpg",8,$lang['host'][$spielersprache[8]]['destabilisator'][0],array($p_name,$sektork)); }
                            if (($spieler_9>=1) and ($p_besitzer<>9)) { neuigkeiten(4,"../bilder/news/star_explode.jpg",9,$lang['host'][$spielersprache[9]]['destabilisator'][0],array($p_name,$sektork)); }
                            if (($spieler_10>=1) and ($p_besitzer<>10)) { neuigkeiten(4,"../bilder/news/star_explode.jpg",10,$lang['host'][$spielersprache[10]]['destabilisator'][0],array($p_name,$sektork)); }

                            if ($p_besitzer>=1) {
                                neuigkeiten(4,"../bilder/news/star_explode.jpg",$p_besitzer,$lang['host'][$spielersprache[$p_besitzer]]['destabilisator'][1],array($p_name,$sektork));
                            }
                        }
                    }else{
                        neuigkeiten(4,"../bilder/news/star_explode.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['destabilisator'][2],array($p_name,$sektork));
                    }
                }
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////DESTABILISATOR ENDE
        /////////////////////////////////////////////////////////////////////////////////////////////CYBERRITTNIKK ANFANG
        if ($fracht_vorrat>=220 && ($spezialmission==19 || $spezialmission==28) && $s_eigenschaften[$besitzer]['rasse']==$volk) {
            $kolonistengebaut = 220*$cybern;
            $fracht_leute += $kolonistengebaut;
            mysql_query("UPDATE $skrupel_schiffe SET fracht_vorrat=fracht_vorrat-220, fracht_leute=fracht_leute+$kolonistengebaut WHERE id=$shid");
        }
        /////////////////////////////////////////////////////////////////////////////////////////////CYBERRITTNIKK ENDE
        /////////////////////////////////////////////////////////////////////////////////////////////QUARKREORGANISATOR ANFANG
        if ( ($spezialmission==6) || ($spezialmission==26) ){

            $max=$tankfrei;

            if ($fert_quark_vorrat>=1) {
                $max_vorrat=floor($fracht_vorrat/$fert_quark_vorrat);
                if ($max>$max_vorrat) {$max=$max_vorrat;}
            }
            if ($fert_quark_min1>=1) {
                $max_min1=floor($fracht_min1/$fert_quark_min1);
                if ($max>$max_min1) {$max=$max_min1;}
            }
            if ($fert_quark_min2>=1) {
                $max_min2=floor($fracht_min2/$fert_quark_min2);
                if ($max>$max_min2) {$max=$max_min2;}
            }
            if ($fert_quark_min3>=1) {
                $max_min3=floor($fracht_min3/$fert_quark_min3);
                if ($max>$max_min3) {$max=$max_min3;}
            }
            if (113<$max) {$max=113;}

            if ($max>=1) {

                $vorrat_verbrauch=$max*$fert_quark_vorrat;
                $min1_verbrauch=$max*$fert_quark_min1;
                $min2_verbrauch=$max*$fert_quark_min2;
                $min3_verbrauch=$max*$fert_quark_min3;
                $lemin_prod=$max;

                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set fracht_vorrat=fracht_vorrat-$vorrat_verbrauch,fracht_min1=fracht_min1-$min1_verbrauch,fracht_min2=fracht_min2-$min2_verbrauch,fracht_min3=fracht_min3-$min3_verbrauch,lemin=lemin+$lemin_prod where id=$shid");
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////QUARKREORGANISATOR ENDE


        /////////////////////////////////////////////////////////////////////////////////////////////SCHIFF RECYCLEN ANFANG
        if (($spezialmission==2) and ($status==2)) {

            $zeiger2 = mysql_query("SELECT * FROM $skrupel_planeten where x_pos=$kox and y_pos=$koy and besitzer=$besitzer and spiel=$spiel");
            $planetenanzahl = mysql_num_rows($zeiger2);

            if ($planetenanzahl==1) {
                $array2 = mysql_fetch_array($zeiger2);
                $p_id=$array2["id"];
                $p_sternenbasis=$array2["sternenbasis"];
                $p_sternenbasis_id=$array2["sternenbasis_id"];
                $osys_1=$array2["osys_1"];
                $osys_2=$array2["osys_2"];
                $osys_3=$array2["osys_3"];
                $osys_4=$array2["osys_4"];
                $osys_5=$array2["osys_5"];
                $osys_6=$array2["osys_6"];

                if ($p_sternenbasis_id>=1 or $osys_1==17 or $osys_2==17 or $osys_3==17 or $osys_4==17 or $osys_5==17 or $osys_6==17) {

                    $neu_min1=0;
                    $neu_min2=0;
                    $neu_min3=0;

                    $file=$main_verzeichnis.'daten/'.$volk.'/schiffe.txt';
                    $fp = fopen("$file","r");
                    if ($fp) {
                        $zaehler=0;
                        while (!feof ($fp)) {
                            $buffer = fgets($fp, 4096);
                            $schiff[$zaehler]=$buffer;
                            $zaehler++;
                        }
                        fclose($fp);
                    }

                    for ($ik=0;$ik<$zaehler;$ik++) {
                        $schiffwert=explode(':',$schiff[$ik]);
                        if ($schiffwert[1]==$klasseid) {
                            $neu_min1=round($schiffwert[6]/100*85);
                            $neu_min2=round($schiffwert[7]/100*85);
                            $neu_min3=round($schiffwert[8]/100*85);
                        }
                    }
                    $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set kolonisten=kolonisten+$fracht_leute,lemin=lemin+$fracht_lemin,min1=min1+$fracht_min1,min2=min2+$fracht_min2,min3=min3+$fracht_min3,vorrat=vorrat+$fracht_vorrat,cantox=cantox+$fracht_cantox where id=$p_id");
                    $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set min1=min1+$neu_min1,min2=min2+$neu_min2,min3=min3+$neu_min3 where id=$p_id");
                    $zeiger_temp = mysql_query("DELETE FROM $skrupel_schiffe where id=$shid");
                    $zeiger_temp = mysql_query("DELETE FROM $skrupel_anomalien where art=3 and extra like 's:$shid:%'");
                    $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where (flug=3 or flug=4) and zielid=$shid");

                    neuigkeiten(3,"../daten/$volk/bilder_basen/1.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['recycle'][0],array($name,$neu_min1,$neu_min2,$neu_min3));
                }
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////SCHIFF RECYCLEN ENDE

        /////////////////////////////////////////////////////////////////////////////////////////////SCHIFF REPARATUR ANFANG
        if (($spezialmission==14) and ($status==2) and ($schaden>=1)) {

            $reperatur=0;

            $zeiger2 = mysql_query("SELECT id,x_pos,y_pos,besitzer,spiel,sternenbasis,sternenbasis_id,sternenbasis_art FROM $skrupel_planeten where x_pos=$kox and y_pos=$koy and besitzer=$besitzer and spiel=$spiel");
            $planetenanzahl = mysql_num_rows($zeiger2);

            if ($planetenanzahl==1) {
                $array2 = mysql_fetch_array($zeiger2);
                $p_id=$array2["id"];
                $p_sternenbasis=$array2["sternenbasis"];
                $p_sternenbasis_id=$array2["sternenbasis_id"];
                $p_sternenbasis_art=$array2["sternenbasis_art"];

                if (($p_sternenbasis_id>=1) and ($p_sternenbasis_art==0)) {
                    $reperatur=11;
                }
                if (($p_sternenbasis_id>=1) and ($p_sternenbasis_art==3)) {
                    $reperatur=11;
                }
                if (($p_sternenbasis_id>=1) and ($p_sternenbasis_art==1)) {
                    $reperatur=19;
                }


                $zeiger3 = mysql_query("SELECT id,kox,koy,besitzer,fertigkeiten,status FROM $skrupel_schiffe where besitzer=$besitzer and kox=$kox and koy=$koy and status=2 and spiel=$spiel order by id");
                $schiffanzahl3 = mysql_num_rows($zeiger3);

                if ($schiffanzahl3>=1) {

                    for ($ikk=0; $ikk<$schiffanzahl3;$ikk++) {
                        $ok3 = mysql_data_seek($zeiger3,$ikk);

                        $array3 = mysql_fetch_array($zeiger3);
                        $kox=$array3["kox"];
                        $koy=$array3["koy"];
                        $besitzer=$array3["besitzer"];
                        $fertigkeiten=$array3["fertigkeiten"];
                        $status=$array3["status"];
                        $fert_reperatur=intval(substr($fertigkeiten,37,1));

                        if (($fert_reperatur>=1) and ($fert_reperatur>$reperatur)) { $reperatur=$fert_reperatur; }

                    }
                }

                if ($reperatur>=1) {
                    $schaden=$schaden-$reperatur;
                    if ($schaden>=1) {
                        $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set schaden=$schaden where id=$shid");
                    } else {
                        $schaden=0;
                        $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set schaden=0 where id=$shid");
                        neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['reparatur'][0],array($name));
                    }
                }
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////SCHIFF REPARATUR ENDE
        /////////////////////////////////////////////////////////////////////////////////////////////CREW ANHEUERN ANFANG
        if (($spezialmission==23) and ($status==2)) {

            $reperatur=0;

            $zeiger2 = mysql_query("SELECT id,kolonisten,x_pos,y_pos,besitzer,spiel,sternenbasis,sternenbasis_id FROM $skrupel_planeten where x_pos=$kox and y_pos=$koy and besitzer=$besitzer and spiel=$spiel");
            $planetenanzahl = mysql_num_rows($zeiger2);

            if ($planetenanzahl==1) {
                $array2 = mysql_fetch_array($zeiger2);
                $p_id=$array2["id"];
                $p_sternenbasis=$array2["sternenbasis"];
                $p_sternenbasis_id=$array2["sternenbasis_id"];
                $p_kolonisten=$array2["kolonisten"];

                if ($p_sternenbasis_id>=1) {

                    $leute_neu=intval($extra[1]);
                    if ($leute_neu>$p_kolonisten) { $leute_neu=$p_kolonisten; }
                    $p_kolonisten=$p_kolonisten-$leute_neu;
                    $crew=$crew+$leute_neu;

                    $extra[1]='';
                    $extra_neu = implode(":", $extra);

                    $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set crew=$crew,extra='$extra_neu',spezialmission=0 where id=$shid");
                    $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set kolonisten=$p_kolonisten where id=$p_id");
                    if($crew==$crewmax){
                        neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['crew'][0],array($name,$leute_neu));
                    }else{
                        neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['crew'][0],array($name,$leute_neu,$crew));
                    }
                }
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////CREW ANHEUERN ENDE

        /////////////////////////////////////////////////////////////////////////////////////////////TANKEN ANFANG
        if (($spezialmission==1) and ($status==2)) {
            $zeiger2 = mysql_query("SELECT * FROM $skrupel_planeten where x_pos=$kox and y_pos=$koy and spiel=$spiel");
            $planetenanzahl = mysql_num_rows($zeiger2);
            if ($planetenanzahl==1) {
                $array2 = mysql_fetch_array($zeiger2);
                $p_id=$array2["id"];
                $osys_1=$array2["osys_1"];
                $osys_2=$array2["osys_2"];
                $osys_3=$array2["osys_3"];
                $osys_4=$array2["osys_4"];
                $osys_5=$array2["osys_5"];
                $osys_6=$array2["osys_6"];
                $p_lemin=$array2["lemin"];
                $p_besitzer=$array2["besitzer"];
                if((($osys_1==7) or ($osys_2==7) or ($osys_3==7) or ($osys_4==7) or ($osys_5==7)or ($osys_6==7))and($p_besitzer!=$besitzer)){
                    $p_lemin=max(0,($p_lemin-100));
                }
                $lemin_tanken=$leminmax-$lemin;
                if ($lemin_tanken>$p_lemin) {$lemin_tanken=$p_lemin;}
                $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set lemin=lemin-$lemin_tanken where id=$p_id");
                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set lemin=lemin+$lemin_tanken where id=$shid");
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////TANKEN ENDE
        /////////////////////////////////////////////////////////////////////////////////////////////PLANETENBOMBARDEMENT ANFANG
        if (($spezialmission==3) and ($status==2)) {

            $zeiger2 = mysql_query("SELECT * FROM $skrupel_planeten where x_pos=$kox and y_pos=$koy and besitzer<>$besitzer and besitzer>=1 and spiel=$spiel");
            $planetenanzahl = mysql_num_rows($zeiger2);

            if ($planetenanzahl==1) {
                $array2 = mysql_fetch_array($zeiger2);
                $p_id=$array2["id"];
                $osys_1=$array2["osys_1"];
                $osys_2=$array2["osys_2"];
                $osys_3=$array2["osys_3"];
                $osys_4=$array2["osys_4"];
                $osys_5=$array2["osys_5"];
                $osys_6=$array2["osys_6"];
                $p_kolonisten=$array2["kolonisten"];
                $p_minen=$array2["minen"];
                $p_fabriken=$array2["fabriken"];
                $p_abwehr=$array2["abwehr"];
                $p_name=$array2["name"];
                $p_bild=$array2["bild"];
                $p_klasse=$array2["klasse"];
                $p_besitzer=$array2["besitzer"];

                $native_id=$array2["native_id"];
                $native_abgabe=$array2["native_abgabe"];
                $native_fert=$array2["native_fert"];
                $native_kol=$array2["native_kol"];
                $native_fert_schutz=intval(substr($native_fert,21,2));

                if ($beziehung[$besitzer][$p_besitzer]['status']!=5) {

                    $maxcol=100;
                    if (($native_id>=1) and ($native_kol>1)) { $maxcol=$maxcol-$native_fert_schutz; }

                    $staerke_angriff=round(($hanger_anzahl*35)+($torpedoschaden[$projektile_stufe]*$projektile_anzahl)+($strahlenschaden[$energetik_stufe]*$energetik_anzahl));

                    $prozent=round($staerke_angriff/4);
                    $prozente[0]=mt_rand(0,$prozent);
                    $prozente[1]=mt_rand(0,($prozent-$prozente[0]));
                    $prozente[2]=mt_rand(0,($prozent-$prozente[0]-$prozente[1]));
                    $prozente[3]=($prozent-$prozente[0]-$prozente[1]-$prozente[2]);

                    shuffle($prozente);

                    $prozent_kolonisten=$prozente[0];if ($prozent_kolonisten>100) { $prozent_kolonisten=100; }
                    $prozent_minen=$prozente[1];if ($prozent_minen>100) { $prozent_minen=100; }
                    $prozent_fabriken=$prozente[2];if ($prozent_fabriken>100) { $prozent_fabriken=100; }
                    $prozent_abwehr=$prozente[3];if ($prozent_abwehr>100) { $prozent_abwehr=100; }

                    if ($prozent_kolonisten>$maxcol) {$prozent_kolonisten=$maxcol;}

                    $vernichtet_kolonisten=round($p_kolonisten/100*$prozent_kolonisten);
                    $o_kolonisten=$p_kolonisten;
                    $p_kolonisten=$p_kolonisten-$vernichtet_kolonisten;
                    if(($osys_1==7) or ($osys_2==7) or ($osys_3==7) or ($osys_4==7) or ($osys_5==7)or ($osys_6==7)){
                        $p_kolonisten=max(1000,$p_kolonisten);
                        $vernichtet_kolonisten=$o_kolonisten-$p_kolonisten;
                        $prozent_kolonisten=round($vernichtet_kolonisten/$o_kolonisten*100);
                    }
                    $vernichtet_minen=round($p_minen/100*$prozent_minen);
                    $vernichtet_fabriken=round($p_fabriken/100*$prozent_fabriken);
                    $vernichtet_abwehr=round($p_abwehr/100*$prozent_abwehr);

                    $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set kolonisten=$p_kolonisten,minen=minen-$vernichtet_minen,fabriken=fabriken-$vernichtet_fabriken,abwehr=abwehr-$vernichtet_abwehr where id=$p_id");

                    neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['bombardement'][0],array($name,$p_name,$vernichtet_minen,$prozent_minen,$vernichtet_fabriken,$prozent_fabriken,$vernichtet_abwehr,$prozent_abwehr,$vernichtet_kolonisten,$prozent_kolonisten));
                    neuigkeiten(1,"../bilder/planeten/$p_klasse"."_"."$p_bild.jpg",$p_besitzer,$lang['host'][$spielersprache[$p_besitzer]]['bombardement'][1],array($p_name,$vernichtet_minen,$prozent_minen,$vernichtet_fabriken,$prozent_fabriken,$vernichtet_abwehr,$prozent_abwehr,$vernichtet_kolonisten,$prozent_kolonisten));
                }
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////PLANETENBOMBARDEMENT ENDE

        /////////////////////////////////////////////////////////////////////////////////////////////VIRALER ANGRIFF ANFANG
        if (($spezialmission==17) and ($status==2)) {
            $zeiger2 = mysql_query("SELECT * FROM $skrupel_planeten where x_pos=$kox and y_pos=$koy and besitzer<>$besitzer and besitzer>=1 and spiel=$spiel");
            $planetenanzahl = mysql_num_rows($zeiger2);

            if ($planetenanzahl==1) {
                $array2 = mysql_fetch_array($zeiger2);
                $p_id=$array2["id"];
                $p_kolonisten=$array2["kolonisten"];
                $p_name=$array2["name"];
                $p_bild=$array2["bild"];
                $p_klasse=$array2["klasse"];
                $p_besitzer=$array2["besitzer"];
                $osys_1=$array2["osys_1"];
                $osys_2=$array2["osys_2"];
                $osys_3=$array2["osys_3"];
                $osys_4=$array2["osys_4"];
                $osys_5=$array2["osys_5"];
                $osys_6=$array2["osys_6"];
                if(($osys_1!=20) and ($osys_2!=20) and ($osys_3!=20) and ($osys_4!=20) and ($osys_5!=20) and ($osys_6!=20)){
                    if ($beziehung[$besitzer][$p_besitzer]['status']!=5) {
                        $prozent_kolonisten=mt_rand($viralmin,$viralmax);
                        $vernichtet_kolonisten=round($p_kolonisten/100*$prozent_kolonisten);
                        $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set kolonisten=kolonisten-$vernichtet_kolonisten where id=$p_id");
                        neuigkeiten(2,"../bilder/news/epedemie.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['viral'][0],array($name,$p_name,$vernichtet_kolonisten,$prozent_kolonisten));
                        neuigkeiten(1,"../bilder/news/epedemie.jpg",$p_besitzer,$lang['host'][$spielersprache[$p_besitzer]]['viral'][1],array($p_name,$vernichtet_kolonisten,$prozent_kolonisten));
                    }
                }else{
                    neuigkeiten(2,"../bilder/news/epedemie.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['viral'][4],array($name,$p_name));
                    neuigkeiten(1,"../bilder/news/epedemie.jpg",$p_besitzer,$lang['host'][$spielersprache[$p_besitzer]]['viral'][5],array($p_name));
                }
            }
        }
        if (($spezialmission==18) and ($status==2)) {
            $zeiger2 = mysql_query("SELECT * FROM $skrupel_planeten where x_pos=$kox and y_pos=$koy and native_id>=1 and native_kol>0 and spiel=$spiel");
            $planetenanzahl = mysql_num_rows($zeiger2);

            if ($planetenanzahl==1) {
                $array2 = mysql_fetch_array($zeiger2);
                $p_id=$array2["id"];
                $p_name=$array2["name"];
                $p_bild=$array2["bild"];
                $p_klasse=$array2["klasse"];
                $native_id=$array2["native_id"];
                $native_kol=$array2["native_kol"];
                $p_besitzer=$array2["besitzer"];
                $osys_1=$array2["osys_1"];
                $osys_2=$array2["osys_2"];
                $osys_3=$array2["osys_3"];
                $osys_4=$array2["osys_4"];
                $osys_5=$array2["osys_5"];
                $osys_6=$array2["osys_6"];
                if(($osys_1!=20) and ($osys_2!=20) and ($osys_3!=20) and ($osys_4!=20) and ($osys_5!=20) and ($osys_6!=20)){
                    if ($beziehung[$besitzer][$p_besitzer]['status']!=5) {
                        $prozent_native=mt_rand($viralmin,$viralmax);
                        $vernichtet_native=round($native_kol/100*$prozent_native);
                        $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set native_kol=native_kol-$vernichtet_native where id=$p_id");
                        neuigkeiten(2,"../bilder/news/epedemie.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['viral'][2],array($name,$p_name,$vernichtet_native,$prozent_native));
                        if ($p_besitzer>=1) {
                            neuigkeiten(1,"../bilder/news/epedemie.jpg",$p_besitzer,$lang['host'][$spielersprache[$p_besitzer]]['viral'][3],array($p_name,$vernichtet_native,$prozent_native));
                        }
                    }
                }else{
                    neuigkeiten(2,"../bilder/news/epedemie.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['viral'][4],array($name,$p_name));
                    if ($p_besitzer>=1) {
                        neuigkeiten(1,"../bilder/news/epedemie.jpg",$p_besitzer,$lang['host'][$spielersprache[$p_besitzer]]['viral'][6],array($p_name));
                    }
                }
            }
        }

        /////////////////////////////////////////////////////////////////////////////////////////////VIRALER ANGRIFF ENDE


        /////////////////////////////////////////////////////////////////////////////////////////////TERRAFORMING ANFANG
        if (($spezialmission==5) and ($status==2)) {

            $zeiger2 = mysql_query("SELECT * FROM $skrupel_planeten where x_pos=$kox and y_pos=$koy and spiel=$spiel");
            $planetenanzahl = mysql_num_rows($zeiger2);

            if ($planetenanzahl==1) {
                $array2 = mysql_fetch_array($zeiger2);
                $p_id=$array2["id"];
                $p_temp=$array2["temp"];
                $p_name=$array2["name"];

                if ($fert_terra_warm>=1) {
                    $p_temp=$p_temp+$fert_terra_warm;
                    $tempschreib=$p_temp-35;
                    neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['terraforming'][0],array($p_name,$fert_terra_warm,$tempschreib));
                }
                if ($fert_terra_kalt>=1) {
                    $p_temp=$p_temp-$fert_terra_kalt;
                    $tempschreib=$p_temp-35;
                    neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['terraforming'][1],array($p_name,$fert_terra_kalt,$tempschreib));
                }
                $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set temp=$p_temp where id=$p_id");
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////TERRAFORMING ENDE

        /////////////////////////////////////////////////////////////////////////////////////////////SPRUNGTOR ANFANG
        if (($spezialmission==13) and ($status==1) and ($flug==0)) {

            $ok=2;
            $zeiger2 = mysql_query("SELECT y_pos,x_pos,spiel from $skrupel_planeten where (sqrt(((x_pos-$kox)*(x_pos-$kox))+((y_pos-$koy)*(y_pos-$koy)))<=30) and spiel=$spiel");
            $p2anzahl = mysql_num_rows($zeiger2);

            if ($p2anzahl>=1) {
                $ok=1;
                neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['sprungtor'][0]);
            }else{

                $zeiger2 = mysql_query("SELECT y_pos,x_pos,spiel from $skrupel_anomalien where (sqrt(((x_pos-$kox)*(x_pos-$kox))+((y_pos-$koy)*(y_pos-$koy)))<=30) and spiel=$spiel");
                $a2anzahl = mysql_num_rows($zeiger2);

                if ($a2anzahl>=1) {
                    $ok=1;
                    neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['sprungtor'][1]);
                }else{
                    if (($fert_sprungtorbau_min1>$fracht_min1) or ($fert_sprungtorbau_min2>$fracht_min2) or ($fert_sprungtorbau_min3>$fracht_min3) or ($fert_sprungtorbau_lemin>$fracht_lemin)) {
                        $ok=1;
                        neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['sprungtor'][2]);
                    }
                }
            }
            if ($ok==2) {
                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set fracht_min1=fracht_min1-$fert_sprungtorbau_min1,fracht_min2=fracht_min2-$fert_sprungtorbau_min2,fracht_min3=fracht_min3-$fert_sprungtorbau_min3,lemin=lemin-$fert_sprungtorbau_lemin where id=$shid");

                if ($sprungtorbauid>=1) {
                    $zeiger_temp = mysql_query("SELECT * FROM $skrupel_anomalien where id=$sprungtorbauid");
                    $array_temp = mysql_fetch_array($zeiger_temp);
                    $aid=$array_temp["id"];
                    $x_pos_eins=$array_temp["x_pos"];
                    $y_pos_eins=$array_temp["y_pos"];

                    $extra=$sprungtorbauid.":".$x_pos_eins.":".$y_pos_eins;
                    $zeiger2 = mysql_query("INSERT INTO $skrupel_anomalien (art,x_pos,y_pos,extra,spiel) values (2,$kox,$koy,'$extra',$spiel);");

                    $zeiger2 = mysql_query("SELECT * FROM $skrupel_anomalien where x_pos=$kox and y_pos=$koy and spiel=$spiel");
                    $array = mysql_fetch_array($zeiger2);
                    $aid_zwei=$array["id"];
                    $x_pos_zwei=$array["x_pos"];
                    $y_pos_zwei=$array["y_pos"];

                    $extra=$aid_zwei.":".$x_pos_zwei.":".$y_pos_zwei;
                    $zeiger2 = mysql_query("UPDATE $skrupel_anomalien set extra='$extra' where id=$sprungtorbauid;");
                    $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set sprungtorbauid=0,spezialmission=0 where id=$shid");
                    neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['sprungtor'][3]);

                } else {
                    $zeiger_temp = mysql_query("INSERT INTO $skrupel_anomalien (art,x_pos,y_pos,spiel) values (2,$kox,$koy,$spiel);");
                    $zeiger_temp = mysql_query("SELECT * FROM $skrupel_anomalien where x_pos=$kox and y_pos=$koy and spiel=$spiel");
                    $array_temp = mysql_fetch_array($zeiger_temp);
                    $aid=$array_temp["id"];
                    $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set sprungtorbauid=$aid,spezialmission=0 where id=$shid");
                    neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['sprungtor'][4]);
                }
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////SPRUNGTOR ENDE
        /////////////////////////////////////////////////////////////////////////////////////////////Akademieausbildung Anfang
        if(($spezialmission>71) and ($spezialmission<77) and ($masse<100)){
            if($flug==0){
                $zeiger2 = mysql_query("SELECT * FROM $skrupel_planeten where besitzer=$spieler and spiel=$spiel and x_pos=$kox and y_pos=$koy");
                $planeten_anzahl = mysql_num_rows($zeiger2);
                $fert_akademie=0;
                if($planeten_anzahl==1){
                    $ok = mysql_data_seek($zeiger2,0);
                    $array2 = mysql_fetch_array($zeiger2);
                    $osys_1=$array2["osys_1"];
                    $osys_2=$array2["osys_2"];
                    $osys_3=$array2["osys_3"];
                    $osys_4=$array2["osys_4"];
                    $osys_5=$array2["osys_5"];
                    $osys_6=$array2["osys_6"];
                    $sternenbasis_art=$array2["sternenbasis_art"];
                    if((($osys_1==16) or ($osys_2==16) or ($osys_3==16) or ($osys_4==16) or ($osys_5==16) or ($osys_6==16))and($sternenbasis_art==2)and$erfahrung<5){
                        $fert_akademie=1;
                    }
                    for($j=1;$j<=strlen($fertigkeiten);$j++){
                        if(($j<53) or($j>55)){
                            if(intval(substr($fertigkeiten,$j,1))!=0){
                                $fert_akademie=0;
                            }
                        }
                    }
                    if($fert_akademie==1){
                        if($spezialmission==72 and $fracht_cantox>=100 and $fracht_vorrat>=10 and $fracht_lemin>=50){
                            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set fracht_cantox=fracht_cantox-100,fracht_vorrat=fracht_vorrat-10,lemin=lemin-50,erfahrung=erfahrung+1,spezialmission=0 where id=$shid and erfahrung<5 and spiel=$spiel");
                            neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['akademie'][2],array($name));
                        }elseif($spezialmission>72 and $spezialmission<77){
                            $spezialmission--;
                            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set spezialmission=$spezialmission where id=$shid and spiel=$spiel");
                            if($spezialmission>72){
                                neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['akademie'][0],array($name,$spezialmission-71));
                            }else{
                                neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['akademie'][1],array($name));
                            }
                        }
                    }
                }
            }else{
                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set spezialmission=0 where id=$shid and spiel=$spiel");
                neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['akademie'][3],array($name));
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////Akademieausbildung Ende
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////TARNFELD ANFANG

$zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set tarnfeld=1 where spezialmission=8 and spiel=$spiel");
if($module[0]) {
    $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set tarnfeld=0 where spezialmission<>8 and !(volk='unknown' and klasseid=1) and spiel=$spiel");
    $zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where tarnfeld=1 and !(volk='unknown' and klasseid=1) and spiel=$spiel order by id");
}else {
    $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set tarnfeld=0 where spezialmission<>8 and spiel=$spiel");
    $zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where tarnfeld=1 and spiel=$spiel order by id");
}


$schiffanzahl = mysql_num_rows($zeiger);
if ($schiffanzahl>=1) {
    for ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $masse=$array["masse"];
        $fracht_min2=$array["fracht_min2"];

        $min2_brauch=round(($masse/100)+0.5);

        if ($min2_brauch<=$fracht_min2) {
            $fracht_min2=$fracht_min2-$min2_brauch;
            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set fracht_min2=$fracht_min2 where id=$shid");
        } else {
            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set tarnfeld=0 where id=$shid");
        }
    }
}
$zeiger = mysql_query("SELECT name,volk,besitzer,bild_gross,id,tarnfeld,spiel,antrieb FROM $skrupel_schiffe where tarnfeld=1 and spiel=$spiel and antrieb=7 order by id");
$schiffanzahl = mysql_num_rows($zeiger);
if ($schiffanzahl>=1) {
    for ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $name=$array["name"];
        $volk=$array["volk"];
        $besitzer=$array["besitzer"];
        $bild_gross=$array["bild_gross"];

        $zuzahl=mt_rand(1,100);

        if ($zuzahl<=19) {
            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set tarnfeld=0 where id=$shid");
            neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['tarnfeld'][0],array($name));
        }
    }
}
$zeiger = mysql_query("SELECT id,spiel,antrieb,antrieb_anzahl,kox,koy FROM $skrupel_schiffe where spiel=$spiel and antrieb=3 order by id");
$schiffanzahl = mysql_num_rows($zeiger);
if ($schiffanzahl>=1) {
    for ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $kox=$array["kox"];
        $koy=$array["koy"];
        $antrieb_anzahl=$array["antrieb_anzahl"];

        $zuzahl=mt_rand(1,100);

        if ($zuzahl<=($antrieb_anzahl*2)) {

            neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['drugun'][2],array($name));
            $reichweite=117;

            if($module[0]) {
                $zeiger_temp = mysql_query("SELECT id, besitzer, name, volk, bild_gross FROM $skrupel_schiffe where (sqrt(((kox-$kox)*(kox-$kox))+((koy-$koy)*(koy-$koy)))<=$reichweite) and tarnfeld=1 and antrieb<>2 and spiel=$spiel order by id");
                $treffschiff = mysql_num_rows($zeiger_temp);

                if ($treffschiff>=1) {

                    for ($k=0; $k<$treffschiff;$k++) {
                        $ok2 = mysql_data_seek($zeiger_temp,$k);

                        $array_temp = mysql_fetch_array($zeiger_temp);
                        $t_shid=$array_temp["id"];
                        $t_besitzer=$array_temp["besitzer"];
                        $t_name=$array_temp["name"];
                        $t_volk=$array_temp["volk"];
                        $t_bild_gross=$array_temp["bild_gross"];

                        $zeiger_temp2 = mysql_query("UPDATE $skrupel_schiffe set tarnfeld=0 where id=$t_shid and spiel=$spiel");
                        neuigkeiten(2,"../daten/$t_volk/bilder_schiffe/$t_bild_gross",$t_besitzer,$lang['host'][$spielersprache[$t_besitzer]]['drugun'][0],array($t_name));
                    }
                }
                //spion tarner nur dekrementieren
                $zeiger = mysql_query("SELECT id,tarnfeld, besitzer, name, volk, bild_gross FROM $skrupel_schiffe where (sqrt(((kox-$kox)*(kox-$kox))+((koy-$koy)*(koy-$koy)))<=$reichweite) and volk='unknown' and klasseid=1 and spiel=$spiel");
                $schiffanzahl = mysql_num_rows($zeiger);
                if($schiffanzahl>=1) {
                    for($i=0; $i<$schiffanzahl;$i++) {
                        $ok = mysql_data_seek($zeiger,$i);
                        $array = mysql_fetch_array($zeiger);
                        $t_shid=$array["id"];
                        $t_besitzer=$array_temp["besitzer"];
                        $t_name=$array_temp["name"];
                        $t_volk=$array_temp["volk"];
                        $t_bild_gross=$array_temp["bild_gross"];
                        $tarnfeld=$array["tarnfeld"];
                        $tarnfeld--;
                        if($tarnfeld<=0) {
                            $tarnfeld = 0;
                            neuigkeiten(2,"../daten/$t_volk/bilder_schiffe/$t_bild_gross",$t_besitzer,$lang['host'][$spielersprache[$t_besitzer]]['drugun'][1],array($t_name));
                        }else{
                            neuigkeiten(2,"../daten/$t_volk/bilder_schiffe/$t_bild_gross",$t_besitzer,$lang['host'][$spielersprache[$t_besitzer]]['drugun'][0],array($t_name));
                        }
                        $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set tarnfeld=$tarnfeld where id=$t_shid");
                    }
                }
            }else {
                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set tarnfeld=0 where (sqrt(((kox-$kox)*(kox-$kox))+((koy-$koy)*(koy-$koy)))<=$reichweite) and spiel=$spiel and tarnfeld=1");
            }
        }
    }
}
$zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set tarnfeld=1 where antrieb=2 and spiel=$spiel");
/////////////////////////////////////////////////////////////////////////////////////////////TARNFELD ENDE
/////////////////////////////////////////////////////////////////////////////////////////////DRUGUNVERZERRER ANFANG
$zeiger = mysql_query("SELECT fracht_leute,id,name,klasse,kox,koy,volk,besitzer,bild_gross,crew,leichtebt,schwerebt,zusatzmodul,spezialmission,status FROM $skrupel_schiffe where spezialmission=30 and zusatzmodul=6 and status=2 and spiel=$spiel order by id");
$schiffanzahl = mysql_num_rows($zeiger);

if ($schiffanzahl>=1) {

    for  ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);

        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $name=$array["name"];
        $klasse=$array["klasse"];
        $kox=$array["kox"];
        $koy=$array["koy"];
        $volk=$array["volk"];
        $besitzer=$array["besitzer"];
        $bild_gross=$array["bild_gross"];
        $crew=$array["crew"];
        $leichtebt=$array["leichtebt"];
        $schwerebt=$array["schwerebt"];
        $zusatzmodul=$array["zusatzmodul"];
        $spezialmission=$array["spezialmission"];
        $status=$array["status"];
        $fracht_leute=$array["fracht_leute"];

        $zufall=rand(1,100);
        if ($zufall<=67) {
            $reichweite=round($masse/2);
            neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['drugun'][5],array($name,$reichweite));
            $zeiger2 = mysql_query("SELECT * FROM $skrupel_planeten where x_pos=$kox and y_pos=$koy and besitzer=$besitzer and spiel=$spiel");
            $planetenanzahl = mysql_num_rows($zeiger2);

            if ($planetenanzahl==1) {
                $array2 = mysql_fetch_array($zeiger2);
                $p_id=$array2["id"];
                $p_kolonisten=$array2["kolonisten"];
                $p_leichtebt=$array2["leichtebt"];
                $p_schwerebt=$array2["schwerebt"];

                $p_kolonisten=$p_kolonisten+$crew+$fracht_leute;
                $p_leichtebt+=$leichtebt;
                $p_schwerebt+=$schwerebt;

                $zeigertemp = mysql_query("UPDATE $skrupel_planeten set kolonisten=$p_kolonisten,leichtebt=$p_leichtebt,schwererbt=$p_schwerebt where x_pos=$kox and y_pos=$koy and besitzer=$besitzer and spiel=$spiel");

                $zeiger_temp = mysql_query("SELECT id,tarnfeld, besitzer, name, volk, bild_gross FROM $skrupel_schiffe where (sqrt(((kox-$kox)*(kox-$kox))+((koy-$koy)*(koy-$koy)))<=$reichweite) and tarnfeld>0 and spiel=$spiel order by id");
                $treffschiff = mysql_num_rows($zeiger_temp);

                if ($treffschiff>=1) {
                    for($i=0; $i<$treffschiff;$i++) {
                        $ok = mysql_data_seek($zeiger,$i);
                        $array = mysql_fetch_array($zeiger);
                        $t_shid=$array["id"];
                        $t_besitzer=$array_temp["besitzer"];
                        $t_name=$array_temp["name"];
                        $t_volk=$array_temp["volk"];
                        $t_bild_gross=$array_temp["bild_gross"];
                        $tarnfeld=$array["tarnfeld"];
                        $tarnfeld--;
                        if($tarnfeld==0){
                            neuigkeiten(2,"../daten/$t_volk/bilder_schiffe/$t_bild_gross",$t_besitzer,$lang['host'][$spielersprache[$t_besitzer]]['drugun'][3],array($t_name));
                        }else{
                            neuigkeiten(2,"../daten/$t_volk/bilder_schiffe/$t_bild_gross",$t_besitzer,$lang['host'][$spielersprache[$t_besitzer]]['drugun'][4],array($t_name));
                        }
                        $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set tarnfeld=$tarnfeld where id=$t_shid");
                    }
                }
            }
            $zeiger_temp = mysql_query("DELETE FROM $skrupel_schiffe where id=$shid");
            $zeiger_temp = mysql_query("DELETE FROM $skrupel_anomalien where art=3 and extra like 's:$shid:%'");
            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where (flug=3 or flug=4) and zielid=$shid");
        }
    }
}
/////////////////////////////////////////////////////////////////////////////////////////////DRUGUNVERZERRER ENDE
/////////////////////////////////////////////////////////////////////////////////////////////SENSORPHALANX UND LABOR ANFANG

$zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set scanner=0 where spezialmission<>11 and spezialmission<>12 and spiel=$spiel");
$zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set scanner=1 where spezialmission=11 and spiel=$spiel");
$zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set scanner=2 where spezialmission=12 and spiel=$spiel");

/////////////////////////////////////////////////////////////////////////////////////////////SENSORPHALANX UND LABOR ENDE

/////////////////////////////////////////////////////////////////////////////////////////////SPEZIALMISSIONEN ENDE

///////////////////////////////////////////////////////////////////////////////////////////////PLANETEN ANFANG
///////////////////////////////////////////////////////////////////////////////////////////////KOLONISTEN UND TRUPPEN SCHRUMPFEN ANFANG

$zeiger = mysql_query("SELECT id,name,bild,sternenbasis_id,kolonisten,besitzer,spiel,leichtebt,schwerebt,vorrat FROM $skrupel_planeten where besitzer>=1 and kolonisten<1000 and spiel=$spiel order by id");
$planetenanzahl = mysql_num_rows($zeiger);

if ($planetenanzahl>=1) {

    for ($i=0; $i<$planetenanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);

        $array = mysql_fetch_array($zeiger);
        $pid=$array["id"];
        $name=$array["name"];
        $bild=$array["bild"];
        $sternenbasis_id=$array["sternenbasis_id"];
        $leichtebt=$array["leichtebt"];
        $schwerebt=$array["schwerebt"];
        $vorrat=$array["vorrat"];
        $kolonisten=$array["kolonisten"];
        $kolonisten=$kolonisten-mt_rand(50,200);
        if ($kolonisten<1) {
            $weg=0;
            $bodentruppen=$leichtebt+$schwerebt;
            if (($bodentruppen==0) or (($bodentruppen>=1) and ($vorrat==0))) { $weg=1; } else {
                $notwendig=round($bodentruppen*0.15);
                if ($notwendig<15) { $notwendig=15; }
                if ($notwendig>$vorrat) {
                    $zuwenig=$notwendig-$vorrat;
                    $vorrat=0;
                    $draufgehen=round($zuwenig/0.15);
                    $bodentruppen=$bodentruppen-$draufgehen;
                    if ($bodentruppen<1) { $weg=1; } else {
                        if ($draufgehen<=$schwerebt) {
                            $schwerebt=$schwerebt-$draufgehen;
                        } else {
                            $draufgehen=$draufgehen-$schwerebt;
                            $leichtebt=$leichtebt-$draufgehen;
                        }
                    }
                } else {
                    $vorrat=$vorrat-$notwendig;
                }
            }

            if ($weg==1) {
                $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set leichtebt=0,schwerebt=0,kolonisten=0,besitzer=0,auto_minen=0,auto_fabriken=0,abwehr=0,auto_abwehr=0,auto_vorrat=0,vorrat=0,logbuch='' where id=$pid");
                if ($sternenbasis_id>=1) {
                    $zeiger_temp = mysql_query("UPDATE $skrupel_sternenbasen set besitzer=0 where id=$sternenbasis_id");
                }
            } else {
                $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set vorrat=$vorrat,leichtebt=$leichtebt,schwerebt=$schwerebt where id=$pid");
            }
        } else {
            $zeiger_temp = mysql_query("update $skrupel_planeten set kolonisten=$kolonisten where id=$pid");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////KOLONISTEN SCHRUMPFEN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////PLANETEN NEU BESETZEN ANFANG
$neuekolonie = 0;

$zeiger = mysql_query("SELECT * FROM $skrupel_planeten where besitzer=0 and (kolonisten_new>0 or leichtebt_new>0 or schwerebt>0) and spiel=$spiel order by id");
$planetenanzahl = mysql_num_rows($zeiger);

if ($planetenanzahl>=1) {


    for ($i=0; $i<$planetenanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);

        $array = mysql_fetch_array($zeiger);
        $pid=$array["id"];
        $name=$array["name"];
        $bild=$array["bild"];
        $klasse=$array["klasse"];

        $kolonisten=$array["kolonisten"];
        $kolonisten_new=$array["kolonisten_new"];
        $kolonisten_spieler=$array["kolonisten_spieler"];

        $leichtebt_new=$array["leichtebt_new"];
        $schwerebt_new=$array["schwerebt_new"];

        $sternenbasis_id=$array["sternenbasis_id"];

        $zeiger_temp = mysql_query("update $skrupel_planeten set leichtebt=$leichtebt_new,schwerebt=$schwerebt_new,leichtebt_new=0,schwerebt_new=0,besitzer=$kolonisten_spieler,kolonisten=$kolonisten_new,kolonisten_new=0,kolonisten_spieler=0 where id=$pid");
        if ($sternenbasis_id>=1) { $zeiger_temp = mysql_query("UPDATE $skrupel_sternenbasen set besitzer=$kolonisten_spieler where id=$sternenbasis_id;"); }

        if ($kolonisten_new>0) {
            $neuekolonie++;
            neuigkeiten(1,"../bilder/planeten/$klasse"."_"."$bild.jpg",$kolonisten_spieler,$lang['host'][$spielersprache[$kolonisten_spieler]]['besetzen'][0],array($name));
        } else {
            neuigkeiten(1,"../bilder/planeten/$klasse"."_"."$bild.jpg",$kolonisten_spieler,$lang['host'][$spielersprache[$kolonisten_spieler]]['besetzen'][1],array($name));
        }
    }
}
mysql_query("update $skrupel_zugberechnen_daten set neuekolonie=$neuekolonie WHERE sid='$sid'");

///////////////////////////////////////////////////////////////////////////////////////////////PLANETEN NEU BESETZEN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////GROSSER METEORITEN ANFANG
$meteor=mt_rand(1,200);

if ($meteor==1) {

    $zeiger = mysql_query("SELECT id,spiel,besitzer,name,x_pos,y_pos,sternenbasis FROM $skrupel_planeten where spiel=$spiel and sternenbasis=0 order by rand() limit 0,1");
    $array = mysql_fetch_array($zeiger);
    $pid=$array["id"];
    $name=$array["name"];
    $besitzer=$array["besitzer"];
    $x_pos=$array["x_pos"];
    $y_pos=$array["y_pos"];

    $rohstoff_met=mt_rand(7500,10000);
    $rohstoffe[0]=mt_rand(0,$rohstoff_met);
    $rohstoffe[1]=mt_rand(0,($rohstoff_met-$rohstoffe[0]));
    $rohstoffe[2]=mt_rand(0,($rohstoff_met-$rohstoffe[0]-$rohstoffe[1]));
    $rohstoffe[3]=($rohstoff_met-$rohstoffe[0]-$rohstoffe[1]-$rohstoffe[2]);
    shuffle($rohstoffe);

    $lemin=$rohstoffe[0];
    $min1=$rohstoffe[1];
    $min2=$rohstoffe[2];
    $min3=$rohstoffe[3];

    if ($besitzer>=1) {

        $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set planet_lemin=planet_lemin+$lemin,planet_min1=planet_min1+$min1,planet_min2=planet_min2+$min2,planet_min3=planet_min3+$min3,native_id=0,native_name ='',native_art=0,native_art_name='',native_abgabe=0,native_bild='',native_text='',native_fert='',native_kol=0,kolonisten=0,besitzer=0,minen=0,vorrat=0,cantox=0,auto_minen=0,fabriken=0,auto_fabriken=0,abwehr=0,auto_abwehr=0,auto_vorrat=0,logbuch='' where id=$pid");
        for ($k=1;$k<11;$k++) {
            if (($spieler_id_c[$k]>=1) and ($spieler_raus_c[$k]!=1) and ($besitzer!=$k)) { neuigkeiten(4,"../bilder/news/meteor_gross.jpg",$k,$lang['host'][$spielersprache[$k]]['meteoriten'][0],array($name,$x_pos,$y_pos,$rohstoffe[0],$rohstoffe[2],$rohstoffe[1],$rohstoffe[3])); }
        }

        neuigkeiten(4,"../bilder/news/meteor_gross.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['meteoriten'][1],array($name,$x_pos,$y_pos,$rohstoffe[0],$rohstoffe[2],$rohstoffe[1],$rohstoffe[3]));

    } else {

        $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set planet_lemin=planet_lemin+$lemin,planet_min1=planet_min1+$min1,planet_min2=planet_min2+$min2,planet_min3=planet_min3+$min3,native_id=0,native_name ='',native_art=0,native_art_name='',native_abgabe=0,native_bild='',native_text='',native_fert='',native_kol=0 where id=$pid");
        for ($k=1;$k<11;$k++) {
            if (($spieler_id_c[$k]>=1) and ($spieler_raus_c[$k]!=1)) { neuigkeiten(4,"../bilder/news/meteor_gross.jpg",$k,$lang['host'][$spielersprache[$k]]['meteoriten'][0],array($name,$x_pos,$y_pos,$rohstoffe[0],$rohstoffe[2],$rohstoffe[1],$rohstoffe[3])); }
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////GROSSER METEORITEN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////KLEINE METEORITEN ANFANG
$meteore=mt_rand(0,15);

for ($i=0; $i<$meteore;$i++) {

    $zeiger = mysql_query("SELECT id,spiel,besitzer,name,lemin,min1,min2,min3 FROM $skrupel_planeten where spiel=$spiel order by rand() limit 0,1");
    $array = mysql_fetch_array($zeiger);
    $pid=$array["id"];
    $name=$array["name"];
    $besitzer=$array["besitzer"];
    $lemin=$array["lemin"];
    $min1=$array["min1"];
    $min2=$array["min2"];
    $min3=$array["min3"];

    $rohstoff_met=mt_rand(50,200);
    $rohstoffe[0]=mt_rand(0,$rohstoff_met);
    $rohstoffe[1]=mt_rand(0,($rohstoff_met-$rohstoffe[0]));
    $rohstoffe[2]=mt_rand(0,($rohstoff_met-$rohstoffe[0]-$rohstoffe[1]));
    $rohstoffe[3]=($rohstoff_met-$rohstoffe[0]-$rohstoffe[1]-$rohstoffe[2]);
    shuffle($rohstoffe);

    $lemin=$lemin+$rohstoffe[0];
    $min1=$min1+$rohstoffe[1];
    $min2=$min2+$rohstoffe[2];
    $min3=$min3+$rohstoffe[3];

    $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set lemin=$lemin,min1=$min1,min2=$min2,min3=$min3 where id=$pid");

    if ($besitzer>=1) {
        neuigkeiten(2,"../bilder/news/meteor_klein.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['meteoriten'][2],array($name,$rohstoffe[0],$rohstoffe[2],$rohstoffe[1],$rohstoffe[3]));
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////KLEINE METEORITEN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////PIRATEN ANFANG

if (($piraten_mitte>=1) or ($piraten_aussen>=1)) {

    $zeiger = mysql_query("SELECT zusatzmodul,spiel,id,erfahrung,energetik_anzahl,projektile_anzahl,hanger_anzahl,kox,koy,besitzer,status,name,fracht_cantox,fracht_vorrat,fracht_min1,fracht_min2,fracht_min3,techlevel FROM $skrupel_schiffe where status<>2 and techlevel>3 and energetik_anzahl=0 and projektile_anzahl=0 and hanger_anzahl=0 and spiel=$spiel order by id");

    $schiffanzahl = mysql_num_rows($zeiger);
    if ($schiffanzahl>=1) {
        for ($i=0; $i<$schiffanzahl;$i++) {
            $ok = mysql_data_seek($zeiger,$i);
            $array = mysql_fetch_array($zeiger);
            $shid=$array["id"];
            $kox=$array["kox"];
            $koy=$array["koy"];
            $fracht_min1=$array["fracht_min1"];
            $fracht_min2=$array["fracht_min2"];
            $fracht_min3=$array["fracht_min3"];
            $fracht_cantox=$array["fracht_cantox"];
            $fracht_vorrat=$array["fracht_vorrat"];
            $besitzer=$array["besitzer"];
            $name=$array["name"];
            $erfahrung=$array["erfahrung"];
            $zusatzmodul=$array["zusatzmodul"];

            if (($fracht_min1>=1) or ($fracht_min2>=1) or ($fracht_min3>=1) or ($fracht_cantox>=1) or ($fracht_vorrat>=1)) {

                $abstand=(sqrt(((($umfang/2)-$kox)*(($umfang/2)-$kox))+((($umfang/2)-$koy)*(($umfang/2)-$koy))));

                $wahrscheinlichkeit=0;
                if ($piraten_aussen>$piraten_mitte) {

                    $prozent_abstand=100-($abstand*100/($umfang/2));
                    $differenz=$piraten_aussen-$piraten_mitte;

                    $ein_prozent=$differenz/100;

                    $wahrscheinlichkeit=round(($prozent_abstand*$ein_prozent)+$piraten_mitte);
                }
                if ($piraten_aussen<$piraten_mitte) {

                    $prozent_abstand=$abstand*100/($umfang/2);
                    $differenz=$piraten_mitte-$piraten_aussen;

                    $ein_prozent=$differenz/100;

                    $wahrscheinlichkeit=round(($prozent_abstand*$ein_prozent)+$piraten_aussen);
                }
                if ($piraten_aussen==$piraten_mitte) { $wahrscheinlichkeit=$piraten_aussen; }

                $wahrscheinlichkeit=$wahrscheinlichkeit-($erfahrung*5);

                $tech_stark=0;
                $zeiger2 = mysql_query("SELECT techlevel,spiel,id,energetik_anzahl,projektile_anzahl,hanger_anzahl,kox,koy,besitzer,flug,zielid FROM $skrupel_schiffe where flug=4 and zielid=$shid and kox=$kox and koy=$koy and (energetik_anzahl>=1 or projektile_anzahl>=1 or hanger_anzahl>=1) and spiel=$spiel order by id");

                $schiffanzahl2 = mysql_num_rows($zeiger2);
                if ($schiffanzahl2>=1) {
                    for ($i2=0; $i2<$schiffanzahl;$i2++) {
                        $ok2 = mysql_data_seek($zeiger2,$i2);
                        $array2 = mysql_fetch_array($zeiger2);
                        $techlevel=$array2["techlevel"];

                        if ($techlevel>$tech_stark) {$tech_stark=$techlevel;}
                    }
                }

                if ($tech_stark>=1) {$wahrscheinlichkeit=$wahrscheinlichkeit-($tech_stark*$tech_stark);}

                $zufall=mt_rand(1,100);
                if ($zufall<=$wahrscheinlichkeit) {

                    $prozent_ganz=mt_rand($piraten_min,$piraten_max);

                    if ($erfahrung>=1) {
                        $prozent_ganz=$prozent_ganz-($erfahrung*5);
                    }

                    if ($zusatzmodul==8) { $prozent_ganz=round($prozent_ganz*0.27); }

                    if ($prozent_ganz>=1) {

                        $prozent_ganz=$prozent_ganz*5;

                        $prozente[0]=mt_rand(0,$prozent_ganz);
                        $prozente[1]=mt_rand(0,($prozent_ganz-$prozente[0]));
                        $prozente[2]=mt_rand(0,($prozent_ganz-$prozente[0]-$prozente[1]));
                        $prozente[3]=mt_rand(0,($prozent_ganz-$prozente[0]-$prozente[1]-$prozente[2]));
                        $prozente[4]=($prozent_ganz-$prozente[0]-$prozente[1]-$prozente[2]-$prozente[3]);

                        if ($prozente[0]>round($prozent_ganz/5)) { $prozente[0]=round($prozent_ganz/5); }
                        if ($prozente[1]>round($prozent_ganz/5)) { $prozente[1]=round($prozent_ganz/5); }
                        if ($prozente[2]>round($prozent_ganz/5)) { $prozente[2]=round($prozent_ganz/5); }
                        if ($prozente[3]>round($prozent_ganz/5)) { $prozente[3]=round($prozent_ganz/5); }
                        if ($prozente[4]>round($prozent_ganz/5)) { $prozente[4]=round($prozent_ganz/5); }

                        shuffle($prozente);

                        $fracht_min1_weg=ceil($prozente[0]*$fracht_min1/100);
                        $fracht_min2_weg=ceil($prozente[1]*$fracht_min2/100);
                        $fracht_min3_weg=ceil($prozente[2]*$fracht_min3/100);
                        $fracht_cantox_weg=ceil($prozente[3]*$fracht_cantox/100);
                        $fracht_vorrat_weg=ceil($prozente[4]*$fracht_vorrat/100);

                        if (($fracht_min1_weg>=1) or ($fracht_min2_weg>=1) or ($fracht_min3_weg>=1) or ($fracht_cantox_weg>=1) or ($fracht_vorrat_weg>=1)) {

                            $zeiger = mysql_query("UPDATE $skrupel_schiffe set fracht_min1=fracht_min1-$fracht_min1_weg,fracht_min2=fracht_min2-$fracht_min2_weg,fracht_min3=fracht_min3-$fracht_min3_weg,fracht_cantox=fracht_cantox-$fracht_cantox_weg,fracht_vorrat=fracht_vorrat-$fracht_vorrat_weg where id=$shid");

                            neuigkeiten(1,"../bilder/news/piraten.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['piraten'][0],array($name,$fracht_cantox_weg,$fracht_vorrat_weg,$fracht_min1_weg,$fracht_min2_weg,$fracht_min3_weg));

                        }
                    } else {
                        neuigkeiten(1,"../bilder/news/piraten.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['piraten'][1],array($name));
                    }
                } else {
                    if (($zufall<=$wahrscheinlichkeit+($tech_stark*$tech_stark)) and ($tech_stark>=1)) {
                        neuigkeiten(1,"../bilder/news/piraten.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['piraten'][1],array($name));
                    }
                }
            }
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////PIRATEN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////HANDELSABKOMMEN ANFANG

$bonus=18;

for ($zaehler=1;$zaehler<=10;$zaehler++) {
    if ($spieler_id_c[$zaehler]>=1) {
        $zeiger = mysql_query("SELECT count(*) as total FROM $skrupel_planeten where besitzer=$zaehler and spiel=$spiel");
        $array = mysql_fetch_array($zeiger);
        $spieler_planetenanzahl[$zaehler]=$array["total"];
        $spieler_handelbonus[$zaehler]=100;
    }
}

for ($zaehler=1;$zaehler<=10;$zaehler++) {
    if ($spieler_id_c[$zaehler]>=1) {
        for ($zaehler2=1;$zaehler2<=10;$zaehler2++) {
            if (($spieler_id_c[$zaehler2]>=1) and ($zaehler!=$zaehler2)) {
                if (($beziehung[$zaehler][$zaehler2]['status']==2) && ($spieler_planetenanzahl[$zaehler]>0)) {
                    $spieler_handelbonus[$zaehler]=$spieler_handelbonus[$zaehler]+($spieler_planetenanzahl[$zaehler2]/$spieler_planetenanzahl[$zaehler]*$bonus);
                }
            }
        }
		$spieler_handelbonus[$zaehler]=(round($spieler_handelbonus[$zaehler]))/100;
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////HANDELSABKOMMEN ENDE
///////////////////////////////////////////////////////////////////////////////////////////////PLANETEN START

$zeiger = mysql_query("SELECT * FROM $skrupel_planeten where besitzer>=1 and spiel=$spiel order by id");
$planetenanzahl = mysql_num_rows($zeiger);

if ($planetenanzahl>=1) {

    for ($i=0; $i<$planetenanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);

        $array = mysql_fetch_array($zeiger);
        $pid=$array["id"];
        $name=$array["name"];
        $x_pos=$array["x_pos"];
        $y_pos=$array["y_pos"];
        $bild=$array["bild"];
        $temp=$array["temp"];
        $klasse=$array["klasse"];
        $minen=$array["minen"];
        $cantox=$array["cantox"];
        $vorrat=$array["vorrat"];
        $fabriken=$array["fabriken"];
        $abwehr=$array["abwehr"];
        $besitzer=$array["besitzer"];
        $leichtebt=$array["leichtebt"];
        $schwerebt=$array["schwerebt"];
        $leichtebt_bau=$array["leichtebt_bau"];
        $schwerebt_bau=$array["schwerebt_bau"];

        $auto_minen=$array["auto_minen"];
        $auto_fabriken=$array["auto_fabriken"];
        $auto_vorrat=$array["auto_vorrat"];
        $auto_abwehr=$array["auto_abwehr"];

        $kolonisten=$array["kolonisten"];

        $lemin=$array["lemin"];
        $min1=$array["min1"];
        $min2=$array["min2"];
        $min3=$array["min3"];

        $artefakt=$array["artefakt"];

        $planet_lemin=$array["planet_lemin"];
        $planet_min1=$array["planet_min1"];
        $planet_min2=$array["planet_min2"];
        $planet_min3=$array["planet_min3"];

        $konz_lemin=$array["konz_lemin"];
        $konz_min1=$array["konz_min1"];
        $konz_min2=$array["konz_min2"];
        $konz_min3=$array["konz_min3"];

        $native_id=$array["native_id"];
        $native_abgabe=$array["native_abgabe"];
        $native_fert=$array["native_fert"];
        $native_kol=$array["native_kol"];
        $native_art=$array["native_art"];

        $osys_anzahl=$array["osys_anzahl"];
        $osys_1=$array["osys_1"];
        $osys_2=$array["osys_2"];
        $osys_3=$array["osys_3"];
        $osys_4=$array["osys_4"];
        $osys_5=$array["osys_5"];
        $osys_6=$array["osys_6"];

        $osys = array();

        $osys[1]=$array["osys_1"];
        $osys[2]=$array["osys_2"];
        $osys[3]=$array["osys_3"];
        $osys[4]=$array["osys_4"];
        $osys[5]=$array["osys_5"];
        $osys[6]=$array["osys_6"];


        $native_fert_minen=intval(substr($native_fert,0,3))/100;
        $native_fert_fabriken=intval(substr($native_fert,3,3))/100;
        $native_fert_wachstum=intval(substr($native_fert,23,3))/100;
        $native_fert_prod_vorrat=intval(substr($native_fert,16,1));
        $native_fert_prod_lemin=intval(substr($native_fert,17,1));
        $native_fert_attacke=intval(substr($native_fert,18,3))/100;
        $native_fert_intens=intval(substr($native_fert,26,1));
        $native_fert_klau=intval(substr($native_fert,27,1));

        if ($native_fert_intens==1) {
            $konz_lemin=5;
            $konz_min1=5;
            $konz_min2=5;
            $konz_min3=5;
        }
        if ($native_fert_intens==2) {
            $konz_lemin=1;
            $konz_min1=1;
            $konz_min2=1;
            $konz_min3=1;
        }

        if (($native_id>=1) and ($native_kol>1) and ($native_fert_prod_vorrat>0)) {
            $vorrat=$vorrat+round($native_kol/10000*$native_fert_prod_vorrat);
            $zeigertemp = mysql_query("UPDATE $skrupel_planeten set vorrat=$vorrat where id=$pid");
        }
        if (($native_id>=1) and ($native_kol>1) and ($native_fert_prod_lemin>0)) {
            $lemin=$lemin+round($native_kol/10000*$native_fert_prod_lemin);
            $zeigertemp = mysql_query("UPDATE $skrupel_planeten set lemin=$lemin where id=$pid");
        }

        $zufall=mt_rand(1,100);
        if ($zufall<=18) {
            if ($artefakt==1) {
                $zeigertemp = mysql_query("UPDATE $skrupel_planeten set artefakt=0 where id=$pid");
                $zufall2=mt_rand(500,1500);
                $planet_lemin=$planet_lemin+$zufall2;
                $zeigertemp = mysql_query("UPDATE $skrupel_planeten set planet_lemin=$planet_lemin where id=$pid");
                neuigkeiten(1,"../bilder/news/vorkommen_lemin.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['mineralader'],array($name,'Lemin'));
            }
            if ($artefakt==2) {
                $zeigertemp = mysql_query("UPDATE $skrupel_planeten set artefakt=0 where id=$pid");
                $zufall2=mt_rand(500,1500);
                $planet_min1=$planet_min1+$zufall2;
                $zeigertemp = mysql_query("UPDATE $skrupel_planeten set planet_min1=$planet_min1 where id=$pid");
                neuigkeiten(1,"../bilder/news/vorkommen_min1.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['mineralader'],array($name,'Baxterium'));
            }
            if ($artefakt==3) {
                $zeigertemp = mysql_query("UPDATE $skrupel_planeten set artefakt=0 where id=$pid");
                $zufall2=mt_rand(500,1500);
                $planet_min2=$planet_min2+$zufall2;
                $zeigertemp = mysql_query("UPDATE $skrupel_planeten set planet_min2=$planet_min2 where id=$pid");
                neuigkeiten(1,"../bilder/news/vorkommen_min2.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['mineralader'],array($name,'Rennurbin'));
            }
            if ($artefakt==4) {
                $zeigertemp = mysql_query("UPDATE $skrupel_planeten set artefakt=0 where id=$pid");
                $zufall2=mt_rand(500,1500);
                $planet_min3=$planet_min3+$zufall2;
                $zeigertemp = mysql_query("UPDATE $skrupel_planeten set planet_min3=$planet_min3 where id=$pid");
                neuigkeiten(1,"../bilder/news/vorkommen_min3.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['mineralader'],array($name,'Vomisaan'));
            }
        }
        if ($zufall<=33) {
            if (($artefakt==6) and ($osys_anzahl<6)) {
                $zeigertemp = mysql_query("UPDATE $skrupel_planeten set artefakt=0 where id=$pid");
                $osys_anzahl++;
                $osys[$osys_anzahl]=14;
                if ($osys_anzahl==1) { $osys_1==14;$spalte='osys_1'; }
                if ($osys_anzahl==2) { $osys_2==14;$spalte='osys_2'; }
                if ($osys_anzahl==3) { $osys_3==14;$spalte='osys_3'; }
                if ($osys_anzahl==4) { $osys_4==14;$spalte='osys_4'; }
                if ($osys_anzahl==5) { $osys_5==14;$spalte='osys_5'; }
                if ($osys_anzahl==6) { $osys_6==14;$spalte='osys_6'; }
                $zeigertemp = mysql_query("UPDATE $skrupel_planeten set $spalte=14,osys_anzahl=$osys_anzahl where id=$pid");
                neuigkeiten(1,"../bilder/news/wetterstation.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['wetterstation'],array($name));
            }
        }
        if ($zufall<=50) {
            if ($artefakt==5) {
                $zeigertemp = mysql_query("UPDATE $skrupel_planeten set artefakt=0 where id=$pid");
                $zufall2=mt_rand(2000,10000);
                $kolonisten=$kolonisten+$zufall2;
                $zeigertemp = mysql_query("UPDATE $skrupel_planeten set kolonisten=$kolonisten where id=$pid");
                neuigkeiten(1,"../bilder/news/splitterkolonie.jpg",$besitzer,$lang['host'][$spielersprache[$besitzer]]['kolonisten'],array($name,$zufall2));
            }
        }

        if ($vorrat<0) {$vorrat=0;}
        if ($fabriken<0) {$fabriken=0;}
        if ($minen<0) {$minen=0;}
        if ($cantox<0) {$cantox=0;}

        $rasse = $s_eigenschaften[$besitzer]['rasse'];

        // TEMPERATURANPASSUNG DURCH WETTERSTATION
        if (in_array (14, $osys)) {
            if ($temp!=$r_eigenschaften[$rasse]['temperatur']) {
                    if ($r_eigenschaften[$rasse]['temperatur']==0) {
                        if ($klasse==1) { $temp=mt_rand(40,60);
                        }elseif ($klasse==2) { $temp=mt_rand(30,50);
                        }elseif ($klasse==3) { $temp=mt_rand(0,10);
                        }elseif ($klasse==4) { $temp=mt_rand(50,75);
                        }elseif ($klasse==5) { $temp=mt_rand(86,100);
                        }elseif ($klasse==6) { $temp=mt_rand(70,95);
                        }elseif ($klasse==7) { $temp=mt_rand(75,90);
                        }elseif ($klasse==8) { $temp=mt_rand(20,35);
                        }elseif ($klasse==9) { $temp=mt_rand(25,45);}
                    } else {
                        $temp=$r_eigenschaften[$rasse]['temperatur'];
                    }
                $zeigertemp = mysql_query("UPDATE $skrupel_planeten set temp=$temp where id=$pid");
            }
        }

        //BAUEN VON BODENTRUPPEN ANFANG
        if (($leichtebt_bau>=1) or ($schwerebt_bau>=1)) {
            $leichtebt=$leichtebt+$leichtebt_bau;
            $schwerebt=$schwerebt+$schwerebt_bau;
            $zeigertemp = mysql_query("UPDATE $skrupel_planeten set leichtebt=$leichtebt,leichtebt_bau=0,schwerebt=$schwerebt,schwerebt_bau=0 where id=$pid");
        }
        //BAUEN VON BODENTRUPPEN ENDE
        //SCHIFFE IM ORBIT TARNEN ANFANG
        if (($osys_1==5) or ($osys_2==5) or ($osys_3==5) or ($osys_4==5) or ($osys_5==5) or ($osys_6==5)) {
            $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set tarnfeld=1 where status=2 and kox=$x_pos and koy=$y_pos and spiel=$spiel");
        }
        //SCHIFFE IM ORBIT TARNEN ENDE

        //NATIVE ANFANG KAMPF
        if (($native_id>=1) and ($native_kol>1) and ($native_fert_attacke>0)) {
            $native_leute=mt_rand(50,5000);

            if ($native_leute>$native_kol) {
                $native_leute=$native_kol;
            }
            $besitzer_stark=$r_eigenschaften[$rasse]['bodenverteidigung'];
            $verteidiger=($kolonisten+($leichtebt*16)+($schwerebt*60))*$besitzer_stark;
            $angreifer=$native_leute*$native_fert_attacke;

            if ($verteidiger>=$angreifer) {
                $kolonisten=round($kolonisten-($angreifer*$kolonisten/$verteidiger));
                $leichtebt=round($leichtebt-($angreifer*$leichtebt/$verteidiger));
                $schwerebt=round($schwerebt-($angreifer*$schwerebt/$verteidiger));
                $native_kol=$native_kol-$native_leute;
            }
            if ($angreifer>$verteidiger) {
                $native_kol=$native_kol-round($verteidiger/$native_fert_attacke);
                $kolonisten=0;
                $leichtebt=0;
                $schwerebt=0;
            }
            $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set native_kol=$native_kol,kolonisten=$kolonisten,leichtebt=$leichtebt,schwerebt=$schwerebt where id=$pid");
        }
        //NATIVE ENDE KAMPF
        $Bankbonus=1;
        if($osys_1==3 or $osys_2==3 or $osys_3==3 or $osys_4==3 or $osys_5==3 or $osys_6==3){$Bankbonus=1.075;}
        $reservat=0;
        if($osys_1==23 or $osys_2==23 or $osys_3==23 or $osys_4==23 or $osys_5==23 or $osys_6==23){$reservat=10000;}
        //NATIVE ANFANG
        if (($native_id>=1) and ($native_kol<1000000)) {
            $schwank=mt_rand(1,2);
            if($reservat and($native_kol>$reservat)){$schwank=1;}
            if ($schwank==1) {
                $native_kol=round($native_kol-($native_kol*0.01745));
                if ($native_kol<0) {$native_kol=0;}
            } else {
                $native_kol=round(($native_kol*0.01745)+$native_kol);
            }
            $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set native_kol=$native_kol where id=$pid");
        }
        if (($native_id>=1) and ($native_kol>1)) {
            $cantox=$cantox+round($native_kol*0.008*$native_abgabe*$Bankbonus);
        }
        //NATIVE ENDE
        //NATIVE ASSIMILIEREN ANFANG
        if (($native_id>=1) and ($native_kol>1) and ($r_eigenschaften[$rasse]['assgrad']>=1) and ($kolonisten>1)) {
            if (($r_eigenschaften[$rasse]['assart']==$native_art) or ($r_eigenschaften[$rasse]['assart']==0)) {

                $ueberlauf=round($kolonisten/100*$r_eigenschaften[$rasse]['assgrad']);
                if ($ueberlauf>=1) {
                    if (($ueberlauf-$reservat)>=$native_kol) {
                        $ueberlauf=$native_kol;
                        $kolonisten=$kolonisten+$ueberlauf;
                        $native_kol=0;
                        $native_id=0;
                        $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set kolonisten=$kolonisten,native_id=0,native_kol=0 where id=$pid");
                    } else {
                        $kolonisten=$kolonisten+max(0,$ueberlauf-$reservat);
                        $native_kol=$native_kol-max(0,$ueberlauf-$reservat);
                        $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set kolonisten=$kolonisten,native_kol=$native_kol where id=$pid");
                    }
                }
            }
        }
        //NATIVE ASSIMILIEREN ENDE
        //KOLONISTEN ANFANG
        if (($kolonisten>=1000) and ($kolonisten<10000000)) {

            $temp_unterschied=$temp-$r_eigenschaften[$rasse]['temperatur'];
            if ($temp_unterschied<0) { $temp_unterschied=$temp_unterschied*(-1); }

            if ($r_eigenschaften[$rasse]['temperatur']==0) { $temp_unterschied=0; }

            if ($temp_unterschied<=30) {

                $wachstum=(0.1745-($temp_unterschied*0.004886666666666));
                if ($r_eigenschaften[$rasse]['pklasse']==$klasse) { $wachstum=$wachstum*1.20;}
                if($native_id>0 && $native_kol>1 && $native_fert_wachstum>0) { $wachstum *= $native_fert_wachstum; }
                if($osys_1==6 or $osys_2==6 or $osys_3==6 or $osys_4==6 or $osys_5==6 or $osys_6==6){$wachstum=0.1+$wachstum;}
                $kolonisten=round(($kolonisten/10*$wachstum)+$kolonisten);
                $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set kolonisten=$kolonisten where id=$pid");
            }
        }
        $cantox=$cantox+round($kolonisten*0.008*$r_eigenschaften[$rasse]['steuern']*$spieler_handelbonus[$besitzer]*$Bankbonus);

        $zeiger_temp = mysql_query("update $skrupel_planeten set cantox=$cantox where id=$pid");
        //KOLONISTEN ENDE

        //FABRIKEN BAUEN VORRAT ANFANG

        $fabriken_fert_temp=$fabriken;
        if (($native_id>=1) and ($native_kol>1) and ($native_fert_fabriken>0)) {
            $fabriken_fert_temp=round(($fabriken*$native_fert_fabriken));
        }
        if (($osys_1==1) or ($osys_2==1) or ($osys_3==1) or ($osys_4==1) or ($osys_5==1) or ($osys_6==1)) { $fabriken_fert_temp=round($fabriken_fert_temp*1.15); }

        $vorrat_neu=round($fabriken_fert_temp*$r_eigenschaften[$rasse]['fabriken']);

        $vorrat_klau=0;
        if ($native_fert_klau==5) {
            $vorrat_klau=round($vorrat_neu/100*mt_rand(30,80));
        }
        $vorrat=$vorrat+$vorrat_neu-$vorrat_klau;
        $zeiger_temp = mysql_query("update $skrupel_planeten set vorrat=$vorrat where id=$pid");

        //FABRIKEN BAUEN VORRAT ENDE


        //MINEN PRODUZIEREN ANFANG
        if ($minen>0) {

            $mineralgesamt=$planet_lemin+$planet_min1+$planet_min2+$planet_min3;

            $minen_fert_temp=$minen;
            if (($native_id>=1) and ($native_kol>1) and ($native_fert_minen>0)) {
                $minen_fert_temp=round(($minen*$native_fert_minen)+0.5);
            }

            if (($osys_1==2) or ($osys_2==2) or ($osys_3==2) or ($osys_4==2) or ($osys_5==2) or ($osys_6==2)) { $minen_fert_temp=round($minen_fert_temp*1.09); }

            if ($mineralgesamt>=1) {
                //Feld gibt an wieviel Minen je Kt Mineral benoetigt werden.
                $minen_je_kt_mineral=array(10,6,4,2,1);

                //Lemin Anfang
                $minen_lemin=$planet_lemin*$minen_fert_temp*$r_eigenschaften[$rasse]['minen']/$mineralgesamt;

                $lemin_neu=min($planet_lemin,floor($minen_lemin/max($minen_je_kt_mineral[$konz_lemin-1],1)));

                $mineral_klau=0;
                if ($native_fert_klau==1) {
                    $mineral_klau=round($lemin_neu/100*mt_rand(30,80));
                }

                $lemin=$lemin+$lemin_neu-$mineral_klau;
                $planet_lemin=$planet_lemin-$lemin_neu;
                //Lemin Ende

                //Baxterium Anfang
                $minen_min1=$planet_min1*$minen_fert_temp*$r_eigenschaften[$rasse]['minen']/$mineralgesamt;

				$min1_neu=min($planet_min1,floor($minen_min1/max($minen_je_kt_mineral[$konz_min1-1],1)));

                $mineral_klau=0;
                if ($native_fert_klau==2) {
                    $mineral_klau=round($min1_neu/100*mt_rand(30,80));
                }

                $min1=$min1+$min1_neu-$mineral_klau;
                $planet_min1=$planet_min1-$min1_neu;
                //Baxterium Ende

                //Rennurbin Anfang
                $minen_min2=$planet_min2*$minen_fert_temp*$r_eigenschaften[$rasse]['minen']/$mineralgesamt;

				$min2_neu=min($planet_min2,floor($minen_min2/max($minen_je_kt_mineral[$konz_min2-1],1)));

                $mineral_klau=0;
                if ($native_fert_klau==3) {
                    $mineral_klau=round($min2_neu/100*mt_rand(30,80));
                }

                $min2=$min2+$min2_neu-$mineral_klau;
                $planet_min2=$planet_min2-$min2_neu;
                //Rennurbin Ende

                //Vormissan Anfang
                $minen_min3=$planet_min3*$minen_fert_temp*$r_eigenschaften[$rasse]['minen']/$mineralgesamt;

				$min3_neu=min($planet_min3,floor($minen_min3/max($minen_je_kt_mineral[$konz_min3-1],1)));

                $mineral_klau=0;
                if ($native_fert_klau==4) {
                    $mineral_klau=round($min3_neu/100*mt_rand(30,80));
                }

                $min3=$min3+$min3_neu-$mineral_klau;
                $planet_min3=$planet_min3-$min3_neu;
                //Vormissan Ende

                $zeiger_temp = mysql_query("UPDATE $skrupel_planeten set lemin=$lemin,min1=$min1,min2=$min2,min3=$min3,planet_lemin=$planet_lemin,planet_min1=$planet_min1,planet_min2=$planet_min2,planet_min3=$planet_min3 where id=$pid");
            }
        }
        //MINEN PRODUZIEREN ENDE


        $metro_fabriken_plus=0;
        $metro_minen_plus=0;
        if(($osys_1==9) or ($osys_2==9) or ($osys_3==9) or ($osys_4==9) or ($osys_5==9) or ($osys_6==9)){
            $metro_fabriken_plus=12;
            $metro_minen_plus=24;
        }
        //AUTOMATISCHES FABRIKENBAUEN ANFANG
        if ($auto_fabriken==1) {

            $max_cantox=floor($cantox/3);
            $max_vorrat=$vorrat;

            if ($max_cantox<=$max_vorrat) { $max_bau=$max_cantox; }
            if ($max_vorrat<=$max_cantox) { $max_bau=$max_vorrat; }

            if (($kolonisten/100)<=100) { $max_col=floor($kolonisten/100)+$metro_fabriken_plus; } else { $max_col=100+floor(sqrt($kolonisten/100))+$metro_fabriken_plus; }

            $max_fabriken=$fabriken+$max_bau;
            if ($max_fabriken>$max_col) { $max_fabriken=$max_col;$max_bau=$max_col-$fabriken;}

            if ($max_fabriken>200+$metro_fabriken_plus) {
                $max_fabriken=200+$metro_fabriken_plus;
                $max_bau=200-$fabriken+$metro_fabriken_plus;
            }

            $fabriken=$fabriken+$max_bau;
            $cantox=$cantox-($max_bau*3);
            $vorrat=$vorrat-$max_bau;

            $zeiger_temp = mysql_query("update $skrupel_planeten set fabriken=$fabriken,cantox=$cantox,vorrat=$vorrat where id=$pid");
        }
        //AUTOMATISCHES FABRIKENBAUEN ENDE

        //FABRIKENABBAU ANFANG
        if (($kolonisten/100)<=100) { $max_col=floor($kolonisten/100)+$metro_fabriken_plus; } else { $max_col=100+floor(sqrt($kolonisten/100))+$metro_fabriken_plus; }
        if ($fabriken>$max_col) {
            $prozent=round($fabriken-($fabriken/10));
        if ($prozent>$max_col) { $fabriken=$prozent; } else { $fabriken=$max_col; }
            $zeiger_temp = mysql_query("update $skrupel_planeten set fabriken=$fabriken where id=$pid");
        }
        //FABRIKENABBAU ENDE

        //AUTOMATISCHES MINENBAUEN ANFANG
        if ($auto_minen==1) {

            $max_cantox=floor($cantox/4);
            $max_vorrat=$vorrat;

            if ($max_cantox<=$max_vorrat) { $max_bau=$max_cantox; }
            if ($max_vorrat<=$max_cantox) { $max_bau=$max_vorrat; }

            if (($kolonisten/100)<=200) { $max_col=floor($kolonisten/100)+$metro_minen_plus; } else { $max_col=200+floor(sqrt($kolonisten/100))+$metro_minen_plus; }

            $max_minen=$minen+$max_bau;
            if ($max_minen>$max_col) { $max_minen=$max_col;$max_bau=$max_col-$minen;}

            if ($max_minen>400+$metro_minen_plus) {
                $max_minen=400+$metro_minen_plus;
                $max_bau=400-$minen+$metro_minen_plus;
            }

            $minen=$minen+$max_bau;
            $cantox=$cantox-($max_bau*4);
            $vorrat=$vorrat-$max_bau;

            $zeiger_temp = mysql_query("update $skrupel_planeten set minen=$minen,cantox=$cantox,vorrat=$vorrat where id=$pid");
        }
        //AUTOMATISCHES MINENBAUEN ENDE

        //MINENABBAU ANFANG
        if (($kolonisten/100)<=200) { $max_col=floor($kolonisten/100)+$metro_minen_plus; } else { $max_col=200+floor(sqrt($kolonisten/100))+$metro_minen_plus; }
        if ($minen>$max_col) {
            $prozent=round($minen-($minen/10));
            if ($prozent>$max_col) { $minen=$prozent; } else { $minen=$max_col; }
            $zeiger_temp = mysql_query("update $skrupel_planeten set minen=$minen where id=$pid");
        }
        //MINENABBAU ENDE

        //AUTOMATISCHES ABWEHRANLAGENBAUEN ANFANG
        if ($auto_abwehr==1) {

            $max_cantox=floor($cantox/10);
            $max_vorrat=$vorrat;

            if ($max_cantox<=$max_vorrat) { $max_bau=$max_cantox; }
            if ($max_vorrat<=$max_cantox) { $max_bau=$max_vorrat; }

            if (($kolonisten/100)<=50) { $max_col=floor($kolonisten/100); } else { $max_col=50+floor(sqrt($kolonisten/100)); }

            if (in_array(11,$osys)) { $max_col=floor($max_col*1.5); }

            $max_abwehr=$abwehr+$max_bau;
            if ($max_abwehr>$max_col) { $max_abwehr=$max_col;$max_bau=$max_col-$abwehr;}

            if ($max_abwehr>300) {
                $max_abwehr=300;
                $max_bau=300-$abwehr;
            }


            $abwehr=$abwehr+$max_bau;
            $cantox=$cantox-($max_bau*10);
            $vorrat=$vorrat-$max_bau;

            $zeiger_temp = mysql_query("update $skrupel_planeten set abwehr=$abwehr,cantox=$cantox,vorrat=$vorrat where id=$pid");
        }
        //AUTOMATISCHES ABWEHRANLAGENBAUEN ENDE

        //ABWEHRANLAGENABBAU ANFANG
        if (($kolonisten/100)<=50) { $max_col=floor($kolonisten/100); } else { $max_col=50+floor(sqrt($kolonisten/100)); }

        if (in_array(11,$osys)) { $max_col=floor($max_col*1.5); }

        if ($abwehr>$max_col) {
            $prozent=round($abwehr-($abwehr/10));
            if ($prozent>$max_col) { $abwehr=$prozent; } else { $abwehr=$max_col; }
            $zeiger_temp = mysql_query("update $skrupel_planeten set abwehr=$abwehr where id=$pid");
        }
        //ABWEHRANLAGENABBAU ENDE

        //AUTOMATISCHER VORRATVERKAUF ANFANG
        if ($auto_vorrat==1) {

            $cantox=$cantox+$vorrat;
            $vorrat=0;
            $zeiger_temp = mysql_query("update $skrupel_planeten set vorrat=$vorrat,cantox=$cantox where id=$pid");
        }
        //AUTOMATISCHER VORRATVERKAUF ENDE
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////PLANETEN ENDE

?>