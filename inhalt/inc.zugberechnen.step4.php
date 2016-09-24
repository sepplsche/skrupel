<?php

///////////////////////////////////////////////////////////////////////////////////////////////ROUTEBEAMEN ANFANG
$zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where status=2 and routing_status=2 and spiel=$spiel order by id");
$schiffanzahl = mysql_num_rows($zeiger);

if ($schiffanzahl>=1) {
    for ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $besitzer=$array["besitzer"];
        $routing_id=$array["routing_id"];
        $routing_tank=$array["routing_tank"];
        $routing_schritt=$array["routing_schritt"];
        $routing_mins=$array["routing_mins"];
        $routing_rohstoff=(int)$array["routing_rohstoff"];
        $routing_id_temp=explode(":",$routing_id);
        $zid=$routing_id_temp[$routing_schritt];
        $routing_mins_temp=explode(":",$routing_mins);
        $mins=$routing_mins_temp[$routing_schritt];

        $r_option[0]=(int)substr($mins,0,1);
        $r_option[1]=(int)substr($mins,1,1);
        $r_option[2]=(int)substr($mins,3,1);
        $r_option[3]=(int)substr($mins,4,1);
        $r_option[4]=(int)substr($mins,5,1);
        $r_option[5]=(int)substr($mins,7,7);
        $r_option[6]=(int)substr($mins,14,4);
        $r_option[7]=(int)substr($mins,18,4);

        $mins_lemin=substr($mins,2,1);
        $voll_laden=substr($mins,6,1);

        $r_fracht[0]=(int)$array["fracht_cantox"];
        $r_fracht[1]=(int)$array["fracht_vorrat"];
        $r_fracht[2]=(int)$array["fracht_min1"];
        $r_fracht[3]=(int)$array["fracht_min2"];
        $r_fracht[4]=(int)$array["fracht_min3"];
        $r_fracht[5]=(int)$array["fracht_leute"];
        $r_fracht[6]=(int)$array["leichtebt"];
        $r_fracht[7]=(int)$array["schwerebt"];

        $fracht_lemin=$array["lemin"];
        $frachtraum=$array["frachtraum"];
        $leminmax=$array["leminmax"];

        $kox=$array["kox"];
        $koy=$array["koy"];

        $r_faktor[0]=0; //cantox
        $r_faktor[1]=100; //vorrat
        $r_faktor[2]=100; //bax
        $r_faktor[3]=100; //ren
        $r_faktor[4]=100; //vor
        $r_faktor[5]=1; //kol
        $r_faktor[6]=30;  //lbt
        $r_faktor[7]=150; //sbt

        $freiraum=$frachtraum*100;
        $freitank=$leminmax-$fracht_lemin;

        for($zaehler=0;$zaehler<8;$zaehler++){
            $freiraum-=$r_fracht[$zaehler]*$r_faktor[$zaehler];
        }



        $zeiger2 = mysql_query("SELECT * FROM $skrupel_planeten where x_pos=$kox and y_pos=$koy and besitzer=$besitzer and id=$zid and spiel=$spiel");
        $planetenanzahl = mysql_num_rows($zeiger2);

        if ($planetenanzahl==1) {
            $array2 = mysql_fetch_array($zeiger2);
            $p_id=$array2["id"];
            $p_lemin=$array2["lemin"];

            $r_planet[0]=(int)$array2["cantox"];
            $r_planet[1]=(int)$array2["vorrat"];
            $r_planet[2]=(int)$array2["min1"];
            $r_planet[3]=(int)$array2["min2"];
            $r_planet[4]=(int)$array2["min3"];
            $r_planet[5]=(int)$array2["kolonisten"];
            $r_planet[6]=(int)$array2["leichtebt"];
            $r_planet[7]=(int)$array2["schwerebt"];


            //ausladen
            for($zaehler=0;$zaehler<8;$zaehler++){
                if ($r_option[$zaehler]==2) {
                    $r_planet[$zaehler]+=$r_fracht[$zaehler];
                    $freiraum+=$r_fracht[$zaehler]*$r_faktor[$zaehler];
                    $r_fracht[$zaehler]=0;
                }
            }

            //ausladen relativ
            for($zaehler=5;$zaehler<8;$zaehler++){
                if (($r_option[$zaehler]>2)and($r_option[$zaehler]>$r_planet[$zaehler])){
                    $zwischen=min($r_option[$zaehler]-$r_planet[$zaehler],$r_fracht[$zaehler]);
                    $r_planet[$zaehler]+=$zwischen;
                    $r_fracht[$zaehler]-=$zwischen;
                    $freiraum+=$zwischen*$r_faktor[$zaehler];
                }
            }

            //einladen wichtigstes Gut
            if ($r_option[$routing_rohstoff]==1){
                if (($r_planet[$routing_rohstoff]*$r_faktor[$routing_rohstoff])<=$freiraum) {
                    $freiraum=$freiraum-($r_planet[$routing_rohstoff]*$r_faktor[$routing_rohstoff]);
                    $r_fracht[$routing_rohstoff]+=$r_planet[$routing_rohstoff];
                    $r_planet[$routing_rohstoff]=0;
                }else{
                    $r_planet[$routing_rohstoff]=$r_planet[$routing_rohstoff]-(int)floor($freiraum/$r_faktor[$routing_rohstoff]);
                    $r_fracht[$routing_rohstoff]+=(int)floor($freiraum/$r_faktor[$routing_rohstoff]);
                    $freiraum-=(int)floor($freiraum/$r_faktor[$routing_rohstoff])*$r_faktor[$routing_rohstoff];
                }
            }elseif(($r_option[$routing_rohstoff]>2)and($r_option[$routing_rohstoff]<$r_planet[$routing_rohstoff])){
                    $zwischen=min($r_planet[$routing_rohstoff]-$r_option[$routing_rohstoff],(int)floor($freiraum/$r_faktor[$routing_rohstoff]));
                    $r_planet[$routing_rohstoff]-=$zwischen;
                    $r_fracht[$routing_rohstoff]+=$zwischen;
                    $freiraum-=$zwischen*$r_faktor[$routing_rohstoff];
            }

            //einladen relativ
            for($zaehler=5;$zaehler<8;$zaehler++){
                if (($r_option[$zaehler]>2)and($r_option[$zaehler]<$r_planet[$zaehler])){
                    $zwischen=min($r_planet[$zaehler]-$r_option[$zaehler],(int)floor($freiraum/$r_faktor[$zaehler]));
                    $r_planet[$zaehler]-=$zwischen;
                    $r_fracht[$zaehler]+=$zwischen;
                    $freiraum-=$zwischen*$r_faktor[$zaehler];
                }
            }

            //einladen cantox(da sonst divison durch null)
            if ($r_option[0]==1){
                $r_fracht[0]+=$r_planet[0];
                $r_planet[0]=0;
            }

            //einladen(Vorr�te zum schlu�)
            $ztest=1;
            $zaehler=2;
            while($ztest==1){
                if($zaehler==1){$ztest=0;}
                if ($r_option[$zaehler]==1){
                    if (($r_planet[$zaehler]*$r_faktor[$zaehler])<=$freiraum) {
                        $freiraum=$freiraum-($r_planet[$zaehler]*$r_faktor[$zaehler]);
                        $r_fracht[$zaehler]+=$r_planet[$zaehler];
                        $r_planet[$zaehler]=0;
                    }else{
                        $r_planet[$zaehler]=$r_planet[$zaehler]-(int)floor($freiraum/$r_faktor[$zaehler]);
                        $r_fracht[$zaehler]+=(int)floor($freiraum/$r_faktor[$zaehler]);
                        $freiraum-=(int)floor($freiraum/$r_faktor[$zaehler])*$r_faktor[$zaehler];
                    }
                }
                $zaehler=($zaehler==7)?1:$zaehler+1;
            }

            //rest
            if ($mins_lemin==1) {
                if ($p_lemin<=$freitank) {
                    $freitank=$freitank-$p_lemin;
                    $fracht_lemin=$fracht_lemin+$p_lemin;
                    $p_lemin=0;
                }else{
                    $p_lemin=$p_lemin-$freitank;
                    $fracht_lemin=$fracht_lemin+$freitank;
                    $freitank=0;
                }
            }
            if ($mins_lemin==2) {
                $p_lemin=$p_lemin+$fracht_lemin;
                $fracht_lemin=0;
            }

            if (($fracht_lemin<$routing_tank) and ($p_lemin>0)) {
                $fehlt=$routing_tank-$fracht_lemin;
                if ($fehlt<=$p_lemin) {
                    $p_lemin=$p_lemin-$fehlt;$fracht_lemin=$routing_tank;
                }else{
                    $fracht_lemin=$fracht_lemin+$p_lemin;$p_lemin=0;
                }
            }

            $s_cantox=$r_planet[0];
            $s_vorrat=$r_planet[1];
            $s_bax=$r_planet[2];
            $s_ren=$r_planet[3];
            $s_vor=$r_planet[4];
            $s_kol=$r_planet[5];
            $s_lbt=$r_planet[6];
            $s_sbt=$r_planet[7];
            $zeigertemp = mysql_query("UPDATE $skrupel_planeten set lemin=$p_lemin,cantox=$s_cantox,vorrat=$s_vorrat,min1=$s_bax,min2=$s_ren,min3=$s_vor,kolonisten=$s_kol,leichtebt=$s_lbt,schwerebt=$s_sbt where id=$p_id");

            $s_cantox=$r_fracht[0];
            $s_vorrat=$r_fracht[1];
            $s_bax=$r_fracht[2];
            $s_ren=$r_fracht[3];
            $s_vor=$r_fracht[4];
            $s_kol=$r_fracht[5];
            $s_lbt=$r_fracht[6];
            $s_sbt=$r_fracht[7];
            $zeigertemp = mysql_query("UPDATE $skrupel_schiffe set fracht_leute=$s_kol,leichtebt=$s_lbt,schwerebt=$s_sbt,lemin=$fracht_lemin,fracht_vorrat=$s_vorrat,fracht_cantox=$s_cantox,fracht_min1=$s_bax,fracht_min2=$s_ren,fracht_min3=$s_vor where id=$shid");
        }
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////ROUTEBEAMEN ENDE

?>
