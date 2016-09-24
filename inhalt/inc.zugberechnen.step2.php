<?php
///////////////////////////////////////////////////////////////////////////////////////////////FLUG ANFANG
//alte Koordinaten und temp_verfolgt nullen
mysql_query("UPDATE $skrupel_schiffe SET kox_old=0,koy_old=0,temp_verfolgt=0 WHERE spiel=$spiel");
//Verfolger auf 1 setzen
mysql_query("UPDATE $skrupel_schiffe SET temp_verfolgt=1 WHERE spiel=$spiel AND flug>2");

//setze temp_verfolgt auf 1 bei allen Schiffen die verfolgt werden
$zeiger = mysql_query("SELECT DISTINCT zielid FROM $skrupel_schiffe where flug>2 and spiel=$spiel");
while($array = mysql_fetch_array($zeiger)) {
    $zid = $array['zielid'];
    mysql_query("UPDATE $skrupel_schiffe SET temp_verfolgt=1 WHERE spiel=$spiel AND id=$zid ");
}

$zeiger = mysql_query("SELECT id,zielid,flug,kox,koy FROM $skrupel_schiffe WHERE flug>0 AND status>0 AND spiel=$spiel AND temp_verfolgt=1 ORDER BY zielid DESC");
$schiffanzahl = mysql_num_rows($zeiger);
if($schiffanzahl>0){
    for  ($i=0; $i< $schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $array = mysql_fetch_array($zeiger);
        //erzeuge das Arbeitsarray mit dem wir arbeiten
        $feld_shid[$i]=$array["id"];
        $feld_zielid[$i]=$array["zielid"];
        $feld_flug[$i]=$array["flug"];
        $feld_kox[$i]=$array["kox"];
        $feld_koy[$i]=$array["koy"];
        $feld_flags[$i]=0;
        $feld_schlange[$i]=0;
    }
    //Beginn der Herstellung der Abarbeitungsreihenfolge fuer den Flug

    for  ($i=0; $i< $schiffanzahl;$i++) {
        //nur noch nicht bearbeitete Objekte bearbeiten
        if($feld_schlange[$i]==0){
            $j=$i;
            $anzahl_schlange=0;
            $abbruch_1=1;
            while($abbruch_1){
                $anzahl_schlange++;
                $feld_flags[$j]=$anzahl_schlange;
                $test_ob_zielid_vorhanden=array_search($feld_zielid[$j],$feld_shid);
                if($feld_shid[$test_ob_zielid_vorhanden]==$feld_zielid[$j]){
                    $test_ob_zielid_vorhanden++;
                }else{
                }
                //Test ob ein Schiff das Ende einer normalen Schlange ist
                if(($feld_flug[$j]>2)&&($test_ob_zielid_vorhanden!=false)){
                    $j=$test_ob_zielid_vorhanden-1;
                    //hier haben wir einen Kreis entdeckt
                    if($feld_flags[$j]) {
                        $zwischenwert=-$feld_flags[$j]-1;
                        $j=$i;
                        $abbruch_2=1;
                        do{
                            //wir setzen alle Schiffe in der Schlange die vor dem Kreis sind auf -1< je nach position in der Schlange und alle im Kreis auf -1
                            if($feld_flags[$j]==0){
                                $abbruch_2=0;
                            }else{
                                $feld_schlange[$j]=min(++$zwischenwert,-1);
                                $feld_flags[$j]=0;
                                $j=array_search($feld_zielid[$j],$feld_shid);
                            }
                        }while($abbruch_2);
                        $abbruch_1=0;
                    }else{
                        //hier treffen wir das Ende einer Schon bearbeiteten Schlange
                        if($feld_schlange[$j]!=0){
                            $abbruch_1=0;
                            if($feld_schlange[$j]< 0){
                                //hier treffen wir eine Minusschlange also eine die in einem Kreis endet
                                //wir fuegen also noch betragsmaessig groessere Minuswerte an
                                $zwischenwert=$feld_schlange[$j]-$anzahl_schlange;
                                $j=$i;
                                do{
                                    $abbruch_2=0;
                                    $feld_schlange[$j]=$zwischenwert++;
                                    if($feld_flags[$j]==0){
                                        $abbruch_2=0;
                                    }
                                    $feld_flags[$j]=0;
                                    $j=array_search($feld_zielid[$j],$feld_shid);
                                }while($abbruch_2);
                            }else{
                                //hier treffen wir das ende einer Ordentlichen Schlange wir merken uns wieder die Positionen erhoeht um die des Endes auf das wir trafen
                                $zwischenwert=$feld_schlange[$j]+$anzahl_schlange;
                                $j=$i;
                                for($k=0;$k<$anzahl_schlange;$k++){
                                    $feld_schlange[$j]=$zwischenwert-$k;
                                    $feld_flags[$j]=0;
                                    $j=array_search($feld_zielid[$j],$feld_shid);
                                }
                            }
                        }
                    }
                }else{
                    //hier haben wir also eine ordentlich schlange die mit eine Schiff endet das kein anderes verfolgt
                    $abbruch_1=0;
                    $j=$i;
                    //wir merken uns schonmal welche wir Schiffe bearbeitet haben und auch an welcehr position sie in der schlange waren
                    for($k=0;$k< $anzahl_schlange;$k++){
                        $feld_schlange[$j]=$anzahl_schlange-$k;
                        $feld_flags[$j]=0;
                        $j=array_search($feld_zielid[$j],$feld_shid);
                    }
                }
            }
        }
    }
}
//So wir haben jetzt folgende Situation:
//Alle Schiffe in Normalen schlangen dh die Mit einem Schiff beginnen das nix verfogt(wert1)und mit einem/mehreren Enden die nicht verfogt werden(groesserer schlangepositionswert) haben einen positiven schlangewert
//Alle Schiffe die in einem Kreis enden dh ein oder Mehrer Schiffe im Ringel die sich verfolgen haben  eine -1 wenn sie dierekt im Kreisel sind oder noch betragsmaessig groessere negative werte wenn sie zum Kreis hinfuehren auch hier kann es sich nach hinten spalten

//wir muessen jetzt noch diese Kreisel aufbrechen dies machen wir in dem wir fuer jeden Kreis(jeweil mit -1) das Geometrische Mittel MP der Koordinaten bestimmen und dann das schiff aus dem Kreis welches den Groessten abstand dazu hat als erstes Schiff einer Neuen ordentlichen Schlange nehmen
//mit Endzielpunk MP, so erhalten wir die Geringste abweichung von einem Tatsaechlichem stetigen resultat einer normalen bewegung, dabei muessen wir aber die Zielid der Schiffe in einem Array fuer alle Kreisel vom  ende der schlange speichern um sie nach dem Flug wieder einzusetzen
$kreisel_anzahl=0;
if($schiffanzahl>0){
	$count=0;
    do{
        //wir suchen den ersten kreisel
        $i=array_search(-1,$feld_schlange);
        if($feld_schlange[$i]==-1){
            $schalter=1;
        }else{
            $schalter=0;
        }
        if($schalter){
            $kreisel_anzahl++;
            $j=$i;
            $anzahl_schlange=0;
            $MP_x=0;
            $MP_y=0;
            do{
                $feld_flags[$j]=1;
                $j=array_search($feld_zielid[$j],$feld_shid);
                $MP_x+=$feld_kox[$j];
                $MP_y+=$feld_koy[$j];
                $anzahl_schlange++;
            }while(!$feld_flags[$j]);
            $MP_x=round($MP_x/$anzahl_schlange);
            $MP_y=round($MP_y/$anzahl_schlange);
            //Suche aus schiffen von eben das schiff mit der Groessten entfernung zu MP heraus Speichere zielid shid des schiffes in zweitem array zwischenarray
            $entfernung_1=9;
            for($k=0;$k< $anzahl_schlange;$k++){
                $j=array_search($feld_zielid[$j],$feld_shid);
                $zeiger3 = mysql_query("SELECT warp FROM $skrupel_schiffe where id=$feld_shid[$j] and spiel=$spiel");
                $array3 = mysql_fetch_array($zeiger3);
                $entfernung_2=$array3["warp"];
                //$entfernung_2=((($MP_x-$feld_kox[$j])*($MP_x-$feld_kox[$j]))+(($MP_y-$feld_koy[$j])*($MP_y-$feld_koy[$j])));
                if($entfernung_2<=$entfernung_1){
                    $feldindex_maxentfernung=$j;
                    $entfernung_1=$entfernung_2;
                }
            }
            $zwischenarray_shid[$kreisel_anzahl-1]=$feld_shid[$feldindex_maxentfernung];
            $zwischenarray_zielid[$kreisel_anzahl-1]=$feld_zielid[$feldindex_maxentfernung];
            //neues Temporaeres ziel
            $zeiger2 = mysql_query("UPDATE $skrupel_schiffe set zielx=$MP_x,ziely=$MP_y,zielid=-1 where spiel=$spiel and id=$feld_shid[$feldindex_maxentfernung]");
            //jetzt machen wir endlich aus dem Kreisel eine Schlange
            $ind=$feldindex_maxentfernung;
            for($k=$anzahl_schlange;$k>0;$k--){
                $ind=array_search($feld_zielid[$ind],$feld_shid);
                $feld_schlange[$ind]=$k;
            }
        }
		$count++;
		error_log("Zähler: ".$count);
    }while($schalter);
}
//jetzt muessen wir nur noch die Moegliche seitenbuschel als Aeste an die Schlangen anhaengen
//solange wie Schiff mit Schlange < -1 existiert{
//hier tragen wir ein ob kleiner minus eins oder nicht
for($i=0; $i<$schiffanzahl;$i++) {
    if($feld_schlange[$i]<-1) {
        $j=$i;
        do{
            $j=array_search($feld_zielid[$j],$feld_shid);
        }while($feld_schlange[$j]<1);
        $wert=$feld_schlange[$j]-$feld_schlange[$i];
        $j=$i;
        do{
            $feld_schlange[$j]=--$wert;
            $j=array_search($feld_zielid[$j],$feld_shid);
        }while($feld_schlange[$j]<1);
    }
}
//so jetzt schreiben wir noch die werte der Schlangenfelder auf die temp_verfolgt der DB und dann knnen wir danach sortiert auslesen
//e
for($i=0; $i<$schiffanzahl;$i++) {
    $zeiger = mysql_query("UPDATE $skrupel_schiffe set temp_verfolgt=$feld_schlange[$i] where spiel=$spiel and id=$feld_shid[$i]");
}
//so wir haben es geschaft alles ist wohlgeordnet jetzt kann geflogen werden
$zeiger = mysql_query("SELECT * FROM $skrupel_schiffe where flug>0 and status>0 and spiel=$spiel order by temp_verfolgt");
$schiffanzahl = mysql_num_rows($zeiger);

if ($schiffanzahl>=1) {
    for  ($i=0; $i<$schiffanzahl;$i++) {
        $ok = mysql_data_seek($zeiger,$i);
        $rauswurf=1;
        $array = mysql_fetch_array($zeiger);
        $shid=$array["id"];
        $name=$array["name"];
        $klasse=$array["klasse"];
        $antrieb=$array["antrieb"];
        $klasseid=$array["klasseid"];
        $kox=$array["kox"];
        $koy=$array["koy"];
        $flug=$array["flug"];
        $zielx=$array["zielx"];
        $ziely=$array["ziely"];
        $zielid=$array["zielid"];
        $warp=$array["warp"];
        $volk=$array["volk"];
        $masse=$array["masse"];
        $masse_gesamt=$array["masse_gesamt"];
        $besitzer=$array["besitzer"];
        $bild_gross=$array["bild_gross"];
        $status=$array["status"];
        $fracht_min2=$array["fracht_min2"];
        $routing_status=$array["routing_status"];
        $zusatzmodul=$array["zusatzmodul"];
        $crew=$array["crew"];
        $crewmax=$array["crewmax"];
        $lemin=$array["lemin"];
        $leminmax=$array["leminmax"];
        $schaden=$array["schaden"];
        $flugbonus=1;
        $spritweniger=0;
        $erfahrung=$array["erfahrung"];
        $energetik_anzahl=$array["energetik_anzahl"];
        $projektile_anzahl=$array["projektile_anzahl"];
        $hanger_anzahl=$array["hanger_anzahl"];

        if (($energetik_anzahl==0) and ($projektile_anzahl==0) and ($hanger_anzahl==0)) { $spritweniger=$erfahrung*8; }
        if ($zusatzmodul==2) { $spritweniger=$spritweniger+11; }
        $kox_old=$kox;
        $koy_old=$koy;
        ////////////////////////////
        $spezialmission=$array["spezialmission"];
        $traktor_id=$array["traktor_id"];

        if ($spezialmission==21) {
            $zeiger2 = mysql_query("SELECT id,masse,spiel FROM $skrupel_schiffe where id=$traktor_id and spiel=$spiel");
            $trakanzahl = mysql_num_rows($zeiger2);
            if ($trakanzahl>=1) {
                $array2 = mysql_fetch_array($zeiger2);
                $masse2=$array2["masse"];
                $masse_gesamt=round($masse+($masse2/2));
            } else {
                $zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set spezialmission=0,traktor_id=0 where id=$shid and spiel=$spiel");
            }
        }
        ////////////////////////////overdrive
        $overdrive=0;
        $overdrive_raus=0;

        if (($spezialmission>=61) and ($spezialmission<=69)) {
            $overdrive_stufe=$spezialmission-60;
            $temp=mt_rand(0,100);
            if ($temp<=($overdrive_stufe*10)) {
                $overdrive_raus=1;
                neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['flug'][0],array($name));
            } else {
                $overdrive=1;
                $flugbonus=$flugbonus+($overdrive_stufe*0.1);
            }
        }
        ////////////////////////////
        if ($antrieb==1) { $verbrauchpromonat = array ("0","0","0","0","0","0","0","0","0","0"); }
        if ($antrieb==2) { $verbrauchpromonat = array ("0","100","107.5","300","400","500","600","700","800","900"); }
        if ($antrieb==3) { $verbrauchpromonat = array ("0","100","106.25","107.78","337.5","500","600","700","800","900"); }
        if ($antrieb==4) { $verbrauchpromonat = array ("0","100","103.75","104.44","106.25","300","322.22","495.92","487.5","900"); }
        if ($antrieb==5) { $verbrauchpromonat = array ("0","100","103.75","104.44","106.25","104","291.67","291.84","366.41","900"); }
        if ($antrieb==6) { $verbrauchpromonat = array ("0","100","103.75","104.44","106.25","104","103.69","251.02","335.16","900"); }
        if ($antrieb==7) { $verbrauchpromonat = array ("0","100","103.75","104.44","106.25","104","103.69","108.16","303.91","529.63"); }
        if ($antrieb==8) { $verbrauchpromonat = array ("0","100","100","100","100","100","100","102.04","109.38","529.63"); }
        if ($antrieb==9) { $verbrauchpromonat = array ("0","100","100","100","100","100","100","100","100","100"); }

        if ((($flug==4) or ($flug==3))and ($zielid!=-1)) {
            $zeiger_temp = mysql_query("SELECT id,kox,koy,spezialmission,antrieb,tarnfeld,besitzer,name,bild_gross,volk FROM $skrupel_schiffe where id=$zielid order by id");
            $array_temp = mysql_fetch_array($zeiger_temp);
            $zielxt=$zielx;
            $zielyt=$ziely;
            $name_2=$array_temp["name"];
            $volk_2=$array_temp["volk"];
            $bild_gross_2=$array_temp["bild_gross"];
            $besitzer_2=$array_temp["besitzer"];
            $tarnfeld_2=$array_temp["tarnfeld"];
            $antrieb_2=$array_temp["antrieb"];
            $spezialmission_2=$array_temp["spezialmission"];
            $zielx=$array_temp["kox"];
            $ziely=$array_temp["koy"];
            if(($flug==3)and(($spezialmission_2==8)or($antrieb_2==2))and ($tarnfeld_2< 2)){
				$n_gescannt=1;
			
				// TODO seppl-1: rausfinden wo und warum überall warp*warp gemacht wird, und wo man es mit (warp+warpbonus)*(warp+warpbonus) ersetzen kann
				$scan_temp_reichweite=(($spezialmission==11)?85:(($spezialmission==12)?116:47))+($warp*$warp);
				if((($zielx-$kox)*($zielx-$kox))+(($ziely-$koy)*($ziely-$koy))<=($scan_temp_reichweite*$scan_temp_reichweite)){
					$n_gescannt=0;
				}
                
				$zeiger_temp2 = mysql_query("SELECT besitzer FROM $skrupel_schiffe where (
                    (sqrt(((kox-$zielx)*(kox-$zielx))+((koy-$ziely)*(koy-$ziely)))<=47) and ((spezialmission<>11) and (spezialmission<>12)))
                     or ((sqrt(((kox-$zielx)*(kox-$zielx))+((koy-$ziely)*(koy-$ziely)))<=85) and (spezialmission=11))
                     or  ((sqrt(((kox-$zielx)*(kox-$zielx))+((koy-$ziely)*(koy-$ziely)))<=116) and (spezialmission=12))
                     order by id");
                $anzahl_temp2 = mysql_num_rows($zeiger_temp2);
                if($anzahl_temp2 > 0){
                    for($j=0;$j< $anzahl_temp2; $j++){
                        $ok = mysql_data_seek($zeiger_temp2,$j);
                        $array_temp2 = mysql_fetch_array($zeiger_temp2);
                        $besitzer_temp2=$array_temp2["besitzer"];
                        if(($besitzer==$besitzer_temp2)or($beziehung[$besitzer][$besitzer_temp2]['status']> 3)){
                            $n_gescannt=0;
                        }
                    }
                }
                $zeiger_temp2 = mysql_query("SELECT besitzer FROM $skrupel_planeten where (
                    (sqrt(((x_pos-$zielx)*(x_pos-$zielx))+((y_pos-$ziely)*(y_pos-$ziely)))<=53) and (sternenbasis_art<>3))
                    or ((sqrt(((x_pos-$zielx)*(x_pos-$zielx))+((y_pos-$ziely)*(y_pos-$ziely)))<=116) and (sternenbasis_art=3))
                    order by id");
                $anzahl_temp2 = mysql_num_rows($zeiger_temp2);
                if($anzahl_temp2 > 0){
                    for($j=0;$j< $anzahl_temp2; $j++){
                        $ok = mysql_data_seek($zeiger_temp2,$j);
                        $array_temp2 = mysql_fetch_array($zeiger_temp2);                        
                        $besitzer_temp2=$array_temp2["besitzer"];
                        if(($besitzer==$besitzer_temp2)or($beziehung[$besitzer][$besitzer_temp2]['status']> 3)){
                            $n_gescannt=0;
                        }
                    }
                }
                if($n_gescannt==1){
                    $zielx=$zielxt;
                    $ziely=$zielyt;
                    $sektork=sektor($zielx,$ziely);
                    neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['flug'][7],array($spielerfarbe[$besitzer],$name,$sektork,$spielerfarbe[$besitzer_2],$name_2));
                    neuigkeiten(2,"../daten/$volk_2/bilder_schiffe/$bild_gross_2",$besitzer_2,$lang['host'][$spielersprache[$besitzer_2]]['flug'][8],array($spielerfarbe[$besitzer_2],$name_2,$sektork,$spielerfarbe[$besitzer],$name));
                }
            }
        }

        if ((($kox!=$zielx) or ($koy!=$ziely)) and ($overdrive_raus==0)) {
            $lichtjahre=sqrt(($kox-$zielx)*($kox-$zielx)+($koy-$ziely)*($koy-$ziely));
			$streckemehr=strecke($warp)*$flugbonus;
            if (($status==2) and ($warp<=3) and ($antrieb<=3)) {
				$streckemehr=4*4;
            }
            if ($antrieb==1) {
                $zufall=mt_rand(1,100);
				if ($zufall<=11) {
					$streckemehr=9*9;
					neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['flug'][1],array($name));
				}
			}
			$zeit=$lichtjahre/$streckemehr;

			$verbrauch=$verbrauchpromonat[$warp];
			if ($zeit<=1) {
				$kox=$zielx;$koy=$ziely;
				$verbrauch=floor($lichtjahre*$verbrauch*$masse_gesamt/100000);
			} else {
				$kox=$kox+(($zielx-$kox)/$zeit);
				$koy=$koy+(($ziely-$koy)/$zeit);
				$verbrauch=floor(pow($warp, 2)*$verbrauch*$masse_gesamt/100000);
			}

			$verbrauch=$verbrauch-($verbrauch/100*$spritweniger);
			if ($verbrauch==0) { $verbrauch=1; }
			if ($verbrauchpromonat[$warp]==0) { $verbrauch=0; }

			if (($antrieb==4) and ($verbrauch>=1)) {
				$zufall=mt_rand(1,100);
				if ($zufall<=17) {
					$verbrauchneu=floor(37*($verbrauch/100));
					neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['flug'][2],array($name,$verbrauch,$verbrauchneu));
					$verbrauch=$verbrauchneu;
				}
			}

			if (($verbrauch>$lemin) and ($fracht_min2>=1) and ($fracht_min2+$lemin>=$verbrauch) and ($antrieb==6)) {
				$fehlt=$verbrauch-$lemin;
				$fracht_min2=$fracht_min2-$fehlt;
				$lemin=$verbrauch;
			}

			if ($verbrauch>$lemin) { $rauswurf=2; } else {
				$lemin=$lemin-$verbrauch;

				if ($zeit<=1) {
					if ($flug==1) {
						$flug_neu=0;
						$status=1;
						neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['flug'][3],array($name));
					}
					if ($flug==2) {
						$flug_neu=0;
						$status=2;
						if ($routing_status>=1) {
							neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['flug'][4],array($name));
						} else {
							neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['flug'][5],array($name));
						}
					}
					if ($flug==4) {
						$flug_neu=$flug;
						$status=1;
					}
					if ($flug==3) {
						$flug_neu=0;
						$status=1;
					}
				} else {
					$flug_neu=$flug;
					$status=1;
				}
			}
			if ($rauswurf==1) {

				if (($flug==1) or ($flug==2)) {
					$zeiger2 = mysql_query("UPDATE $skrupel_schiffe set kox_old=$kox_old,koy_old=$koy_old,strecke=strecke+$streckemehr,fracht_min2=$fracht_min2,kox=$kox, koy=$koy, lemin=$lemin, flug=$flug_neu, status=$status where id=$shid");
				}
				if (($flug==4) or ($flug==3)) {
					$zeiger2 = mysql_query("UPDATE $skrupel_schiffe set kox_old=$kox_old,koy_old=$koy_old,strecke=strecke+$streckemehr,fracht_min2=$fracht_min2,kox=$kox, koy=$koy, zielx=$zielx, ziely=$ziely, lemin=$lemin, flug=$flug_neu, status=$status where id=$shid");
				}

				$stat_lichtjahre[$besitzer]=$stat_lichtjahre[$besitzer]+$streckemehr;
				if ($spezialmission==21) {
					$zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set kox=$kox, koy=$koy, status=$status where id=$traktor_id and spiel=$spiel");
				}
			} else {
				$zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set flug=0,warp=0,zielx=0,ziely=0,zielid=0 where id=$shid");
				$zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set routing_schritt=0,routing_status=0,routing_koord='',routing_id='',routing_mins='',routing_warp=0,routing_tank=0,routing_rohstoff=0 where id=$shid");
				neuigkeiten(2,"../daten/$volk/bilder_schiffe/$bild_gross",$besitzer,$lang['host'][$spielersprache[$besitzer]]['flug'][6],array($name));
			}
		}elseif(($overdrive_raus==1)and(($flug==4) or ($flug==3))and ($zielid!=-1)){
			$zeiger_temp = mysql_query("UPDATE $skrupel_schiffe set zielx=$zielx,ziely=$ziely where id=$shid");
        }
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////FLUG ENDE
?>