<?php
include ("../inc.conf.php");
if(!$_GET["sprache"]){$_GET["sprache"]=$language;}
$file="../lang/".$_GET["sprache"]."/lang.uebersicht_imperien.php";
include ($file);

if ($_GET["fu"]==1) {
    include ("inc.header.php");
        $farbe="5f4444";
        
        ?>
        <body text="#ffffff" bgcolor="#444444" link="#000000" vlink="#000000" alink="#000000" leftmargin="0" rightmargin="0" topmargin="0" marginwidth="0" marginheight="0">
            <div id="bodybody" class="flexcroll" onfocus="this.blur()">
            <table border="0" cellspacing="0" cellpadding="0" height="100%" width="100%">
                <tr>
                    <td>
                        <table border="0" cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td><img src="../bilder/empty.gif" border="0" width="10" height="1"></td>
                                <td valign="top">
                                    <center>
                                        <table border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td><center><img src="../lang/<?php echo $_GET["sprache"]?>/topics/siegbedingungen.gif" border="0" width="199" height="52"></center></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <?php if ($ziel_id==0) { ?>
                                                        <center>
                                                            <table border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td><?php echo $lang['uebersichtimperien']['justforfun']?></td>
                                                                </tr>
                                                            </table>
                                                        </center>
                                                    <?php } 
                                                    if ($ziel_id==1) {
    
                                                        $text=str_replace(array('{1}','{2}'),array($ziel_info,$spieleranzahl),$lang['uebersichtimperien']['ueberleben']);
                                                        ?>
                                                        <center>
                                                            <table border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td><?php echo $text?></td>
                                                                </tr>
                                                            </table>
                                                        </center>
                                                    <?php } 
                                                    if ($ziel_id==2) {
    
                                                        $feind=intval($spieler_ziel);
                                                        $feind_id=$spieler_id_c[$feind];
                                                        $zeiger_temp= @mysql_query("SELECT * FROM $skrupel_user where id=$feind_id");
                                                        $array_temp = @mysql_fetch_array($zeiger_temp);
                                                        $username=$array_temp["nick"];
    
                                                        $todfeind="<font color='".$spielerfarbe[$feind]."'>".$username."</font>";
                                                        $text=str_replace(array('{1}'),array($todfeind),$lang['uebersichtimperien']['todfeind']);
                                                        ?>
                                                        <center>
                                                            <table border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td><?php echo $text?></td>
                                                                </tr>
                                                            </table>
                                                        </center>
                                                    <?php } 
                                                    if ($ziel_id==5) {
    
                                                        $text=str_replace(array('{1}','{2}'),array($ziel_info,$spieler_ziel),$lang['uebersichtimperien']['spice']);
                                                        ?>
                                                        <center>
                                                            <table border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td><?php echo $text?></td>
                                                                </tr>
                                                            </table>
                                                        </center>
                                                    <?php } 
                                                    if ($ziel_id==6) {
    
                                                        $zieldaten=explode(':',$spieler_ziel);
                                                        $feinda=intval($zieldaten[1]);
                                                        $feinda_id=$spieler_id_c[$feinda];
                                                        $feindb=intval($zieldaten[2]);
                                                        $feindb_id=$spieler_id_c[$feindb];
    
                                                        $zeiger_temp= @mysql_query("SELECT * FROM $skrupel_user where id=$feinda_id");
                                                        $array_temp = @mysql_fetch_array($zeiger_temp);
                                                        $username=$array_temp["nick"];
                                                        $todfeinda="<font color='".$spielerfarbe[$feinda]."'>".$username."</font>";
    
                                                        $zeiger_temp= @mysql_query("SELECT * FROM $skrupel_user where id=$feindb_id");
                                                        $array_temp = @mysql_fetch_array($zeiger_temp);
                                                        $username=$array_temp["nick"];
                                                        $todfeindb="<font color='".$spielerfarbe[$feindb]."'>".$username."</font>";
    
                                                        $text=str_replace(array('{1}','{2}'),array($todfeinda,$todfeindb),$lang['uebersichtimperien']['teamtodfeind']);
                                                        ?>
                                                        <center>
                                                            <table border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td><?php echo $text?></td>
                                                                </tr>
                                                            </table>
                                                        </center>
                                                    <?php } ?>
    
                                                </td>
                                            </tr>
                                        </table>
                                    </center><br>
    
                                    <center>
                                        <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                            <tr>
                                                <td width="100%" colspan="3"><img src="../lang/<?php echo $_GET["sprache"]?>/topics/dieimperien.gif" border="0" width="185" height="52"></td>
                                                <td><center><img src="<?php echo $bildpfad?>/aufbau/rang_1.gif" border="0" width="41" height="41"></center></td>
                                                <td><center><img src="<?php echo $bildpfad?>/aufbau/rang_2.gif" border="0" width="41" height="41"></center></td>
                                                <td><center><img src="<?php echo $bildpfad?>/aufbau/rang_3.gif" border="0" width="41" height="41"></center></td>
                                                <td><center><img src="<?php echo $bildpfad?>/aufbau/rang_4.gif" border="0" width="113" height="41"></center></td>
                                            </tr>
                                            <?php
    
                                            for ($k=1;$k<11;$k++) {
                                                if ($spieler_id_c[$k]>=1) {
                                                    $zeiger_temp= @mysql_query("SELECT * FROM $skrupel_user where id=$spieler_id_c[$k]");
                                                    $array_temp = @mysql_fetch_array($zeiger_temp);
                                                    $username=$array_temp["nick"];
    
                                                    if ($spieler_raus_c[$k]==1) { $username="<s>".$username."</s>"; }
    
                                                    if ($spieler_raus_c[$k]==0) {
                                                        if ($spieler_zug_c[$k]==1) {
                                                            echo '<tr>';
                                                        } else {
                                                            echo '<tr bgcolor="#'.$farbe.'">';
                                                        }
                                                    }
                                                    ?>
                                                        <td><a href="meta_rassen.php?fu=2&uid=<?php echo $uid?>&sid=<?php echo $sid?>&rasse=<?php echo$spieler_rasse_c[$k]?>&sprache=<?php echo $_GET["sprache"]?>"><img src="../daten/<?php echo $spieler_rasse_c[$k]?>/bilder_allgemein/menu.png" width="186" height="75" border="0"></a></td>
                                                        <td>&nbsp;</td>
                                                        <td style="color:<?php echo $spielerfarbe[$k]?>;font-size:12px;" width="100%"><?php echo $spieler_rassename_c[$k]?><? if ($spieler==$k) {?> <a href="uebersicht_imperien.php?fu=2&spid=<?php echo $spieler?>&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>" style="font-size:9px;">(<?php echo $lang['uebersichtimperien']['edit']?>)</a><?php }?><br><br><nobr><a href="uebersicht_imperien.php?fu=4&spid=<?php echo $spieler?>&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>"><?php echo $username?></a></nobr></td>
                                                        <td style="font-size:11px;"><nobr><center><?php echo $spieler_basen_c[$k]?>.</center></nobr></td>
                                                        <td style="font-size:11px;"><nobr><center><?php echo $spieler_planeten_c[$k]?>.</center></nobr></td>
                                                        <td style="font-size:11px;"><nobr><center><?php echo $spieler_schiffe_c[$k]?>.</center></nobr></td>
                                                        <td style="font-size:12px;"><nobr><b><center><?php echo $spieler_gesamt_c[$k]; ?>.</center></b></nobr></td>
                                                    </tr><?php
                                                }
                                            }
                                            ?>
                                        </table>
                                    </center>
                                </td>
                                <td><img src="../bilder/empty.gif" border="0" width="10" height="1"></td>
                            </tr>
                        </table><br>
                    </td>
                </tr>
            </table>
            </div>
            <?php
    include ("inc.footer.php");
}

if ($_GET["fu"]==2) {
    include ("inc.header.php");

        if ($spieler==$_GET["spid"]) {
            $rassenname=$spieler_rassename_c[$spieler];

            ?>
            <body text="#ffffff" bgcolor="#444444" link="#000000" vlink="#000000" alink="#000000" leftmargin="0" rightmargin="0" topmargin="0" marginwidth="0" marginheight="0">
                <table border="0" cellspacing="0" cellpadding="0" height="100%" width="100%">
                    <tr>
                        <td>
                            <center>
                                <table border="0" cellspacing="0" cellpadding="5">
                                    <form name="formular" method="post" action="uebersicht_imperien.php?fu=3&spid=<?php echo $spieler?>&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>">
                                        <tr>
                                            <td></td>
                                            <td><input type="text" name="neu_name" class="eingabe" value="<?php echo $rassenname?>" maxlength="40" style="width:250px;"></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td><input type="submit" name="bla" value="<?php echo $lang['uebersichtimperien']['umbenennen']?>" style="width:250px;"></td>
                                            <td></td>
                                        </tr>
                                    </form>
                                </table>
                            </center>
                        </td>
                    </tr>
                </table>
                <?php
        }
    include ("inc.footer.php");
}

if ($_GET["fu"]==3) {
    include ("inc.header.php");

        if ($spieler==$_GET["spid"]) {
            $spalte="spieler_".$spieler."_rassename";

            $zeiger_temp= @mysql_query("UPDATE $skrupel_spiele set $spalte='".$_POST['neu_name']."' where id=$spiel");
            ?>
            <script language=JavaScript>
                window.location='uebersicht_imperien.php?fu=1&uid=<?php echo $uid?>&sid=<?php echo $sid?>&sprache=<?php echo $_GET["sprache"]?>';
            </script>
            <?php
        }
    include ("inc.footer.php");
}

if ($_GET["fu"]==4) {
    include ("inc.header.php");
        $spid=$_GET["spid"];
        $zeiger_temp= @mysql_query("SELECT * FROM $skrupel_user where id=$spid");
        $array_temp = @mysql_fetch_array($zeiger_temp);
        $nick=$array_temp["nick"];
        $email=$array_temp["email"];
        $icq=$array_temp["icq"];
        $avatar=$array_temp["avatar"];
        $stat_teilnahme=$array_temp["stat_teilnahme"];
        $stat_sieg=$array_temp["stat_sieg"];
        $stat_schlacht=$array_temp["stat_schlacht"];
        $stat_schlacht_sieg=$array_temp["stat_schlacht_sieg"];
        $stat_kol_erobert=$array_temp["stat_kol_erobert"];
        $stat_lichtjahre=$array_temp["stat_lichtjahre"];
        $stat_monate=$array_temp["stat_monate"];

        ?>
        <body text="#ffffff" bgcolor="#444444"  link="#000000" vlink="#000000" alink="#000000" leftmargin="0" rightmargin="0" topmargin="0" marginwidth="0" marginheight="0">
            <center><img src="../lang/<?php echo $_GET["sprache"]?>/topics/kolonien.gif" border="0" width="162" height="52"></center>
            <center>
                <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td><img src="../lang/<?php echo $_GET["sprache"]?>/topics/kolonien.gif" border="0" width="150" height="150"></td>
                        <td><img src="<?php echo $bildpfad?>/empty.gif" border="0" width="20" height="1"></td>
                    </tr>
                </table>
            </center>
            <?php
    include ("inc.footer.php");
}
?>
