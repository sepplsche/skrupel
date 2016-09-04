<?php

if ($main_verzeichnis!='../') $main_verzeichnis='';
define('DATADIR', $main_verzeichnis.'daten/');
define('INCLUDEDIR', $main_verzeichnis.'inhalt/');
define('LANGUAGEDIR', $main_verzeichnis.'lang/');

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

if(count($sprachen) == 0) {
    include(LANGUAGEDIR.$language.'/lang.inc.host_messenger.php');
} else {
    foreach($sprachen as $sprache) {
        include(LANGUAGEDIR.$sprache.'/lang.inc.host_messenger.php');
    }
}

if ($fuu==1) {
    for ($k=1;$k<11;$k++) {
        if (($spieler_id_c[$k]>=1) and ($spieler_raus_c[$k]==0)) {
            $zeiger = mysql_query("SELECT jabber,optionen FROM $skrupel_user where id=$spieler_id_c[$k]");
            $array = mysql_fetch_array($zeiger);
            $jabberr=$array["jabber"];
            $optionen=$array["optionen"];

            if ((substr($optionen,1,1)=='1') and (strlen($jabberr)>=3)) {

                $hash=$spieler_hash[$k];
                ?>
                <iframe src="inc.host_messenger.php?fu=2&jab=<?php echo $jabberr?>&sname=<?php echo $spiel_name?>&hash=<?php echo $hash?>&k=<?php echo $k?>&sprache=<?php echo $_GET["sprache"]?>" style="border:0px;width:0px;height:0px;" scrolling="no" marginheight="0" marginwidth="0" frameborder="0"></iframe>
                <?php
            }
        }
    }
}

if ($_GET["fu"]==2) {
    include ("../inc.conf.php");

    $sname=$_GET["sname"];
    $jab=$_GET["jab"];
    $hash=$_GET["hash"];
    $msg=str_replace('{1}',$sname,$lang['hostmessenger'][$spielersprache[$_GET["k"]]][0]);

    ignore_user_abort(true);

    $url="http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
    $url=substr($url,0,strlen($url)-30);

    $msg.="\n\n".$url.'/index.php?hash='.$hash;

    // class.jabber.php einbinden
    require_once "classes/class.jabber.php";

    // Ein neues JABBER Objekt erstellen, mit den gewuenschten Zugangsdaten.
    $jabber = new Jabber();
    // Servername f�r den Account
    $jabber->server = $jabber_server;
    // Username f�r den Account
    $jabber->username = $jabber_botname;
    // Klartext-Passwort f�r den Account
    $jabber->password = $jabber_passwort;
    // Die Ressource klassifiziert lediglich einen Account, ist also nur ein Hilfsmerkmal
    $jabber->resource = 'skrupelNotificationBot';
    // Logging f�r Problel�ufe
    $jabber->enable_logging = true;
    // Der Empf�nger f�r meine Nachrichten
    $jabber->to = $jab;

    // Jabber-Verbindung herstellen
    $jabber->Connect();
    $jabber->SendAuth();

    $jabber->SendMessage($jabber->to, "normal", NULL, array("body" => $msg));
}
?>
