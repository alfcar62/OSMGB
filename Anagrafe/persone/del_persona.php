<?php
/*
*** del_persona.php
*** cancellazione casa 
*** Viene attivato da gest_persone.php
*** attiva delete_persona.php
*** 20/02/2020: A. Carlone
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
setup();
isLogged("gestore");
?>
<?php stampaIntestazione(); ?>
<?php stampaNavbar(); 

$id_pers = $_POST['id_pers'];

echo "<br>CANCELLAZIONE PERSONA: identificativo: $id_pers <br><br>";

echo "<form  method='POST' action='delete_persona.php'>";
echo "Si vuole davvero cancellare?<br>";
echo "<input type='submit' name='no' value='no'>&nbsp;";
echo "<input type='submit' name='si' value='si'>";
echo "<input type='hidden' name='id_pers' value='$id_pers'>";

echo "</form>";
echo "</body>";
echo "</html>";
?>

