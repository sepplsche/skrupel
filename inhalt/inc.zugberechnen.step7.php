<?php
///////////////////////////////////////////////////////////////////////////////////////////////MOVIEGIF OPTIONAL ANFANG

$moviegif_verzeichnis = $main_verzeichnis.'extend/moviegif';

if ((@file_exists($moviegif_verzeichnis)) and (intval(substr($spiel_extend,0,1))==1)) {
    include($moviegif_verzeichnis.'/shot.php');
}

///////////////////////////////////////////////////////////////////////////////////////////////MOVIEGIF OPTIONAL END
?>
