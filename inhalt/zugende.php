<?php
/*
:noTabs=false:indentSize=4:tabSize=4:folding=explicit:collapseFolds=1:
*/
include ('../inc.conf.php');
if(!$_GET["sprache"]){$_GET["sprache"]=$language;}
include ("../lang/".$_GET["sprache"]."/lang.zugende.php");
include_once ('inc.hilfsfunktionen.php');

$fuid = int_get('fu');

//fu:1 Zugende Hauptmenu {{{
if ($fuid==1) {
    include ("inc.header.php");

    $zeiger2 = @mysql_query("SELECT count(*) AS total FROM $skrupel_spiele WHERE (spieler_1=$spieler_id or spieler_2=$spieler_id or spieler_3=$spieler_id or spieler_4=$spieler_id or spieler_5=$spieler_id or spieler_6=$spieler_id or spieler_7=$spieler_id or spieler_8=$spieler_id or spieler_9=$spieler_id or spieler_10=$spieler_id) and id<>$spiel and phase=0");
    $array2 = @mysql_fetch_array($zeiger2);

    $weitere = $array2['total'];

    ?>
    <body text="#000000" bgcolor="#444444" style="background-image:url('<?php echo $bildpfad?>/aufbau/14.gif'); background-attachment:fixed;" link="#000000" vlink="#000000" alink="#000000" leftmargin="0" rightmargin="0" topmargin="0" marginwidth="0" marginheight="0">
        <center>
            <table border="0" height="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <?php
                    if($weitere > 0) {
                        ?>
                        <td>
                            <center><a href="zugende.php?fu=7&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>" target="_self"><img src="<?php echo $bildpfad?>/menu/gsprung.gif" width="75" height="75" border="0"><br><nobr><?php echo $lang['zugende']['galaxiesprung']?></nobr></a></center>
                        </td>
                        <?php
                    }
                    ?>
                    <td>
                        <center><a href="zugende.php?fu=2&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>" target="_top"><img src="<?php echo $bildpfad?>/menu/logout.gif" width="75" height="75" border="0"><br><nobr><?php echo $lang['zugende']['logout']?></nobr></a></center>
                    </td>
                    <?php
                    if ($zug_abgeschlossen==0 and $spieler_raus==0){
                        ?>
                        <td>
                            <center><a href="zugende.php?fu=3&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>" target="_self"><img src="<?php echo $bildpfad?>/menu/abschliessen.gif" width="75" height="75" border="0"><br><nobr><?php echo $lang['zugende']['zugabschliessen']?></nobr></a></center>
                        </td>
                        <?php
                    }
                    ?>
                </tr>
            </table>
        </center>
        <?php
    include ("inc.footer.php");
}
//}}}
//fu:2 Spiel verlassen {{{
if ($fuid==2) {
    $conn = @mysql_connect($server.':'.$port,$login,$password);
    $db = @mysql_select_db($database,$conn);

    include ("inc.check.php");

    $zeiger = mysql_query("UPDATE $skrupel_user set uid='',bildpfad='' where id=$spieler_id;");

    $nachricht = $spieler_name.' '.$lang['zugende']['verlassen'];
    $aktuell = time();

    $zeiger = @mysql_query("INSERT INTO $skrupel_chat (spiel,datum,text,an,von,farbe) VALUES ($spiel,'$aktuell','$nachricht',0,'System','000000');");

    @mysql_close();

    if ($bildpfad=='../bilder') { $bildpfad='bilder'; }
    $backlink = "../index.php?pic_path=$bildpfad&sprache=".$_GET["sprache"];
    header ("Location: $backlink");
}
//}}}
//fu:3 Zug abschliessen {{{
if ($fuid==3) {
    $conn = @mysql_connect($server.':'.$port,$login,$password);
    $db = @mysql_select_db($database,$conn);

    include ('inc.check.php');

    $spalte = "spieler_{$spieler}_zug";
    $spieler_zug_c[$spieler] = 1;

    @mysql_query("UPDATE $skrupel_spiele SET $spalte=1 WHERE sid='$sid';");
    $array = mysql_fetch_array(mysql_query("SELECT extend FROM $skrupel_info"));
    $spiel_extend  = $array['extend'];
	
    if (@intval(substr($spiel_extend,1,1))==1) {
        //Wird nur bei installierter, aktiver KI ausgefuehrt. Es wird zunaechst ueberprueft, ob alle
        //menschlichen Spieler ihren Zug beendet haben, damit die KI ihren Zug berechnen kann. Ist dies
        //der Fall, so wird fuer jeden KI-Spieler im aktuellen Spiel ein KI-Objekt erstellt, welches dann
        //den Zug des jeweiligen Spielers berechnet. 
        include("../extend/ki/ki_basis/zugendeKI.php");
    }
    
    @mysql_close();
    
    $fertig = 0;
    for($i=1; $i<=10; $i++) {
        if($spieler_zug_c[$i]==1) $fertig++;
    }    
    
    if($fertig>=$spieleranzahl) {
        $backlink = "zugende.php?fu=6&uid=$uid&sid=$sid&sprache=".$_GET["sprache"];
    } else {
        $backlink = "zugende.php?fu=9&uid=$uid&sid=$sid&sprache=".$_GET["sprache"];
    }
    header ("Location: $backlink");
}
//}}}
//fu:4 Nachricht Zug abgeschlossen {{{
if ($fuid==4) {
    include ('inc.header.php');

    ?>
        <body text="#000000" bgcolor="#444444" style="background-image:url('<?php echo $bildpfad?>/aufbau/14.gif'); background-attachment:fixed;" link="#000000" vlink="#000000" alink="#000000" leftmargin="0" rightmargin="0" topmargin="0" marginwidth="0" marginheight="0">
        <center>
            <table border="0" height="100%" cellspacing="0" cellpadding="0">
                <tr>
                        <td><nobr><center><?php echo $lang['zugende']['abgeschlossen']?></center></nobr></td>
                </tr>
            </table>
        </center>
        <?php
    include ('inc.footer.php');
}
//}}}
//fu:5 Zug berechnen {{{
if ($fuid==-5) {
    include ('inc.header.php');

    $fertig = 0;
    for($i=1; $i<=10; $i++) {
        if($spieler_zug_c[$i]==1) $fertig++;
    }

    if($fertig>=$spieleranzahl) {
        $lasttick = time();
        @mysql_query("UPDATE $skrupel_spiele SET lasttick='$lasttick',spieler_1_zug=0,spieler_2_zug=0,spieler_3_zug=0,spieler_4_zug=0,spieler_5_zug=0,spieler_6_zug=0,spieler_7_zug=0,spieler_8_zug=0,spieler_9_zug=0,spieler_10_zug=0 WHERE sid='$sid';");

        $main_verzeichnis = '../';
        include ('inc.host.php');
    }

    ?>
    <script language=JavaScript>
        function link(url) {
            if (parent.mittelinksoben.document.globals.map.value==1) {
                parent.mittelinksoben.document.globals.map.value=0;
                parent.mittemitte.window.location='aufbau.php?fu=100&query='+url;
            } else  {
                parent.mittemitte.rahmen12.window.location=url;
            }
        }
        function redir() {
            link('uebersicht_uebersicht.php?fu=1&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>');
            window.location='uebersicht.php?fu=1&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>';
        }
    </script>
        <body onload="javascript:redir();" text="#000000" bgcolor="#444444" style="background-image:url('<?php echo $bildpfad?>/aufbau/14.gif'); background-attachment:fixed;" link="#000000" vlink="#000000" alink="#000000" leftmargin="0" rightmargin="0" topmargin="0" marginwidth="0" marginheight="0">
        <center>
            <table border="0" height="100%" cellspacing="0" cellpadding="0">
                <tr>
                <td><nobr><center><?php echo $lang['zugende']['wurdenausgewertet']?></center></nobr></td>
                </tr>
            </table>
        </center>
        <?php
        $fuu=1;
        include ('inc.host_messenger.php');
    include ('inc.footer.php');
}
//}}}
//fu:6 Zug wird berechnet Nachricht und Redirect {{{
if ($fuid==-6) {
    include ('inc.header.php');
    ?>
    <body onLoad="window.location='zugende.php?fu=5&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>';" text="#000000" bgcolor="#444444" style="background-image:url('<?php echo $bildpfad?>/aufbau/14.gif'); background-attachment:fixed;"  link="#000000" vlink="#000000" alink="#000000" leftmargin="0" rightmargin="0" topmargin="0" marginwidth="0" marginheight="0">
        <center>
            <table border="0" cellspacing="0" cellpadding="0" height="100%">
                <tr>
                    <td>
                        <center>
                        <img src="<?php echo $bildpfad?>/radd.gif" height="46" width="51">
                            <br><br>
                            <?php echo $lang['zugende']['wirdberechnet']?>
                        </center>
                    </td>
                </tr>
            </table>
        </center>
        <?php
    include ('inc.footer.php');
}
//}}}
//fu:7 Galaxiesprung, Galaxiewahl {{{
if ($fuid==7) {
    include ('inc.header.php');
    ?>
    <body text="#ffffff" style="background-image:url('<?php echo $bildpfad?>/aufbau/14.gif'); background-attachment:fixed;" bgcolor="#000000" link="#ffffff" vlink="#ffffff" alink="#ffffff" leftmargin="0" rightmargin="0" topmargin="0" marginwidth="0" marginheight="0">
        <center>
            <table border="0" cellspacing="0" cellpadding="1">
                <tr>
                    <td colspan="3"><img src="../bilder/empty.gif" border="0" width="1" height="4"></td>
                </tr>
                <tr>
                    <td><img src="../bilder/empty.gif" border="0" width="17" height="17"></td>
                    <td><center><?php echo $lang['zugende']['galaxiesprung']?></center></td>
                    <td><a href="javascript:hilfe();"><img src="<?php echo $bildpfad?>/icons/hilfe.gif" border="0" width="17" height="17"></a></td>
                </tr>
            </table>
        </center>
        <center>
            <table border="0" cellspacing="0" cellpadding="3">
                <tr>
                    <td colspan="3"><img src="../bilder/empty.gif" border="0" width="1" height="1"></td>
                </tr>
                <tr>
                    <td><form name="formular" method="post" action="zugende.php?fu=8&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>"></td>
                    <td><center><?php $lang['zugende']['sprungwohin']?></center></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <center>
                            <select name="neuesspiel">
                                <?php
                                $zeiger2 = @mysql_query("SELECT * FROM $skrupel_spiele WHERE (spieler_1=$spieler_id or spieler_2=$spieler_id or spieler_3=$spieler_id or spieler_4=$spieler_id or spieler_5=$spieler_id or spieler_6=$spieler_id or spieler_7=$spieler_id or spieler_8=$spieler_id or spieler_9=$spieler_id or spieler_10=$spieler_id) and id<>$spiel and phase=0");
                                if (@mysql_num_rows($zeiger2)>0) {
                                    while ($array = @mysql_fetch_array($zeiger2)) {
                                        $spielneuid=$array["id"];
                                        $spielneuname=$array["name"];
                            
                                        $farbe = '#444444';
                                        for($i=1; $i<=10; $i++) {
                                            $tmpstr = 'spieler_'.$i;
                                            if($spieler_id == $array[$tmpstr] && $array[$tmpstr.'_zug']==0 && $array[$tmpstr.'_raus']==0) $farbe = '#aa0000';
                                        }
                                        ?>
                                        <option value="<?php echo $spielneuid?>" style="background-color:<?php echo $farbe?>"><?php echo $spielneuname?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </center>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3"><img src="../bilder/empty.gif" border="0" width="1" height="1"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><center><input type="submit" name="bla" value="<?php echo $lang['zugende']['sprungdurchfuehren']?>" style="width:250px;"></center></td>
                    <td></form></td>
                </tr>
            </table>
        </center>
        <?php
    include ("inc.footer.php");
}
//}}}
//fu:8 Galaxiesprung durchfuehren {{{
if ($fuid==8) {
    include ("inc.header.php");

    $neuesspiel = int_post('neuesspiel');
    $zeiger2 = @mysql_query("SELECT id,sid FROM $skrupel_spiele WHERE id=$neuesspiel;");

    if (@mysql_num_rows($zeiger2)==1) {
        $array2 = @mysql_fetch_array($zeiger2);
        $sidneu = $array2['sid'];
    }
    ?>
    <script language=JavaScript>
        function link(url) {
            if (parent.mittelinksoben.document.globals.map.value==1) {
                parent.mittelinksoben.document.globals.map.value = 0;
                parent.mittemitte.window.location = 'aufbau.php?fu=100&query=' + url;
            }  else  {
                parent.mittemitte.rahmen12.window.location = url;
            }
        }
        function galaxiewechsel() {
            parent.mittelinksoben.window.location = 'menu.php?fu=1&uid=<?php echo $uid?>&sid=<?php echo $sidneu?>&sprache=<?php echo $_GET["sprache"]?>';
            parent.untenlinks.window.location     = 'menu.php?fu=2&uid=<?php echo $uid?>&sid=<?php echo $sidneu?>&sprache=<?php echo $_GET["sprache"]?>';
            link('uebersicht_uebersicht.php?fu=1&uid=<?php echo $uid?>&sid=<?php echo $sidneu?>&sprache=<?php echo $_GET["sprache"]?>');
            window.location = 'uebersicht.php?fu=1&uid=<?php echo $uid?>&sid=<?php echo $sidneu?>&sprache=<?php echo $_GET["sprache"]?>';
        }
    </script>
    <body onload="javascript:galaxiewechsel();" text="#000000" bgcolor="#444444" style="background-image:url('<?php echo $bildpfad?>/aufbau/14.gif'); background-attachment:fixed;" link="#000000" vlink="#000000" alink="#000000" leftmargin="0" rightmargin="0" topmargin="0" marginwidth="0" marginheight="0">
        <center>
            <table border="0" cellspacing="0" cellpadding="0" height="100%">
                <tr>
                    <td><center><?php echo $lang['zugende']['spunginitialisiert']?></center></td>
                </tr>
            </table>
        </center>
        <?php
    include ("inc.footer.php");
}
//}}}
//fu:9 Zug abschliessen zwischenschritt fuer langsame server oO {{{
if ($fuid==9) {
    $conn = @mysql_connect($server.':'.$port,$login,$password);
    $db = @mysql_select_db($database,$conn);

    include ('inc.check.php');

    $spalte = "spieler_{$spieler}_zug";
    $spieler_zug_c[$spieler] = 1;

    @mysql_query("UPDATE $skrupel_spiele SET $spalte=1 WHERE sid='$sid';");
    @mysql_close();

    $fertig = 0;
    for($i=1; $i<=10; $i++) {
        if($spieler_zug_c[$i]==1) $fertig++;
    }

    if($fertig>=$spieleranzahl) {
        $backlink = "zugende.php?fu=6&uid=$uid&sid=$sid&sprache=".$_GET["sprache"];
    } else {
        $backlink = "zugende.php?fu=4&uid=$uid&sid=$sid&sprache=".$_GET["sprache"];
    }
    header ("Location: $backlink");
}
//}}}

if ($fuid==6) {
    include ('inc.header.php');
    ?>
    <body onLoad="window.location='zugende.php?fu=5&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>';" text="#000000" bgcolor="#444444" style="background-image:url('<?php echo $bildpfad?>/aufbau/14.gif'); background-attachment:fixed;"  link="#000000" vlink="#000000" alink="#000000" leftmargin="0" rightmargin="0" topmargin="0" marginwidth="0" marginheight="0">
        <center>
            <table border="0" cellspacing="0" cellpadding="0" height="100%">
                <tr><td><center><img src="<?php echo $bildpfad?>/radd.gif" height="46" width="51"><br><?php echo $lang['zugende']['wirdberechnet'].'<br>Werte '.last().' Schritte aus...' ?></center></td></tr>
            </table> 
        </center>
        <?php
    include ('inc.footer.php');
}

if ($fuid==5) {
    include ('inc.header.php');

	$step = step($skrupel_zugberechnen, $sid);
	$startstep = $step;
	$last = last();
	$lasttick = time();
	$timeout = $lasttick + 20;

    $fertig = 0;
    for($i=1; $i<=10; $i++) {
        if($spieler_zug_c[$i]==1) $fertig++;
    }
	if($fertig>=$spieleranzahl) {
		@mysql_query("UPDATE $skrupel_spiele SET lasttick='$lasttick',spieler_1_zug=0,spieler_2_zug=0,spieler_3_zug=0,spieler_4_zug=0,spieler_5_zug=0,spieler_6_zug=0,spieler_7_zug=0,spieler_8_zug=0,spieler_9_zug=0,spieler_10_zug=0 WHERE sid='$sid';");
	}
	
	$main_verzeichnis = '../';

	include('inc.host_func.php');
	include('inc.zugberechnen.init.php');
	while(time() < $timeout && $step < $last) {
		// sleep(6);
		$step++;
		include('inc.zugberechnen.step'.$step.'.php');
		@mysql_query("UPDATE $skrupel_zugberechnen SET step=$step WHERE sid='$sid';");
	}

	$redir = "javascript:redirNext();";
	if ($step == $last) {
		$redir = "javascript:redirLast();";
		@mysql_query("UPDATE $skrupel_zugberechnen SET step=0 WHERE sid='$sid';");
	}
    ?>
    <script language=JavaScript>
        function link(url) {
            if (parent.mittelinksoben.document.globals.map.value==1) {
                parent.mittelinksoben.document.globals.map.value=0;
                parent.mittemitte.window.location='aufbau.php?fu=100&query='+url;
            } else  {
                parent.mittemitte.rahmen12.window.location=url;
            }
        }
        function redirLast() {
            link('uebersicht_uebersicht.php?fu=1&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>');
            window.location='uebersicht.php?fu=1&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>';
        }
        function redirNext() {
			window.location='zugende.php?fu=5&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>';
        }
    </script>
	<body onload="<?php echo $redir; ?>" text="#000000" bgcolor="#444444" style="background-image:url('<?php echo $bildpfad?>/aufbau/14.gif'); background-attachment:fixed;" link="#000000" vlink="#000000" alink="#000000" leftmargin="0" rightmargin="0" topmargin="0" marginwidth="0" marginheight="0">
	<center>
		<table border="0" height="100%" cellspacing="0" cellpadding="0">
			<tr><td><nobr><center>
					<img src="<?php echo $bildpfad; ?>/radd.gif" height="46" width="51"><br>
					<?php 
						echo $lang['zugende']['wirdberechnet'].'<br>Schritte '.($startstep+1).' bis '.$step.' von '.$last.' wurden ausgewertet'; 
						if($step < $last) echo ', Fahre fort mit den Schritten '.($step+1).' bis '.$last.'...';
					?>
			</center></nobr></td></tr>
		</table>
	</center>
	<?php
	$fuu=1;
	if ($step == $last) include ('inc.host_messenger.php');
    include ('inc.footer.php');
}

function step($skrupel_zugberechnen, $sid) {
	$zeiger = @mysql_query("SELECT step FROM $skrupel_zugberechnen WHERE sid='$sid';");
	$array = @mysql_fetch_array($zeiger);
	return $array['step'];
}

function last() {
	$found = 0;
	$files = scandir('../inhalt');
	foreach($files as $file) {
		if(strpos($file, 'inc.zugberechnen.step') !== false) {
			$found++;
		}
	}
	return $found;
}
?>
