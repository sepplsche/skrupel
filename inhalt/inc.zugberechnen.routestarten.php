<?php
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
?>
