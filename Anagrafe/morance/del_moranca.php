<?php
/*
*** del_moranca.php
*** cancellazione moranca 
*** Viene attivato dal form di delete_moranca.php
*** 01/03/2020: Gobbi, Arneodo:  Correzione problema durante l'eliminazione delle morançe
*** 20/02/2020: A. Carlone
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
isLogged("utente");
setup();
?>
<?php stampaIntestazione(); ?>
<?php stampaNavbar(); 

$id_moranca = $_POST['id_moranca'];

$query = "SELECT ";
$query .= " m.id, m.nome as nome_moranca, m.id_mor_zona, m.cod_zona,z.nome as desc_zona";
$query .= " FROM morance m, zone z";
$query .= " WHERE m.cod_zona =  z.cod";
$query .= " AND m.id =  $id_moranca";
 
$result = $conn->query($query);
if (!$result )
   throw new Exception($conn->error);

$row = $result->fetch_array();
$desc_zona = $row['desc_zona'];
$nome_moranca = $row['nome_moranca'];

// controllare che non vi siano case nella moranca
// nel caso impedire la cancellazione
echo "<br>CANCELLAZIONE MORANCA: id: $id_moranca - moranca: $nome_moranca - zona:$desc_zona <br><br>";

echo "<form  method='POST' action='delete_moranca.php'>";
echo "Si vuole davvero cancellare?<br>";
echo "<input type='submit' class='button' name='no' value='no'>&nbsp;";
echo "<input type='submit' class='button' name='si' value='si'>";
echo "<input type='hidden' class='button' name='id_moranca' value='$id_moranca'>";

echo "</form>";
echo "</body>";
echo "</html>";
?>

