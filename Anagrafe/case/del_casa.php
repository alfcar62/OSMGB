<?php
/*
*** del_casa.php
*** cancellazione casa 
*** Viene attivato da gest_case.php
*** attiva delete_casa.php
*** 20/02/2020: A. Carlone
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
setup();
?>
<?php stampaIntestazione(); ?>
<?php stampaNavbar(); 
isLogged("utente");
$id_casa = $_POST['id_casa'];

// controllare che non vi siano persone nella casa
// nel caso impedire la cancellazione
echo "<br>CANCELLAZIONE CASA: identificativo: $id_casa <br><br>";

echo "<form  method='POST' action='delete_casa.php'>";
echo "Si vuole davvero cancellare?<br>";
echo "<input type='submit' name='no' value='no'>&nbsp;";
echo "<input type='submit' name='si' value='si'>";
echo "<input type='hidden' name='id_casa' value='$id_casa'>";

echo "</form>";
echo "</body>";
echo "</html>";
?>

