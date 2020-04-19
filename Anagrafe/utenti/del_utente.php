<?php
/*
*** richesta conferma cancellazione
*** Viene attivato da gestione_utenti.php
*** ritorna su gestione_utenti.php dove verrà effettuata l'operazione se si seleziona 'sì'
*** 22/03/2020: Ferraiuolo
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
setup();
isLogged("amministratore");
?>
<?php stampaIntestazione(); ?>
<?php stampaNavbar(); 

$idElimina = $_POST['idElimina'];

// controllare che non vi siano persone nella casa
// nel caso impedire la cancellazione
echo "<br>CANCELLAZIONE utente : $idElimina <br><br>";

echo "<form  method='POST' action='gestione_utenti.php'>";
echo "Si vuole davvero cancellare?<br>";
echo "<input type='submit' name='no' value='no'>&nbsp;";
echo "<input type='submit' name='si' value='si'>";
echo "<input type='hidden' name='idElimina' value='$idElimina'>";

echo "</form>";
echo "</body>";
echo "</html>";
?>
