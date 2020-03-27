<?php
/*
*** modifica_moranca.php
*** modifica moranca, 
*** nella form attiva  mod_moranca.php
*** 15/03/2020: A. Carlone
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
setup();
$pag=$_SESSION['pag_m']['pag_m'];
unset($_SESSION['pag_m']);
?>
<?php
$nome=$_POST["nome_moranca"];
$id_moranca=$_POST["id_moranca"];
$id_osm=$_POST["id_osm"];
$cod_zona=$_POST["cod_zona"];


 $dataOggi=date("Y/m/d");
 try 
  {
   $conn->query("START TRANSACTION"); //inizio transazione

   $query = "SELECT * FROM morance WHERE ID='$id_moranca' ";

   $query = "SELECT ";
   $query .= " m.id, m.nome, m.id_mor_zona, m.cod_zona,";
   $query .= " m.data_inizio_val";
   $query .= " FROM morance m";
   $query .= " WHERE m.id =  $id_moranca";

   $result = $conn->query($query);
   if (!$result )
        throw new Exception($conn->error);

   $row = $result->fetch_array();

   $query  = "INSERT INTO morance_sto (";
   $query .= "TIPO_OP,";
   $query .= "ID_MORANCA,";
   $query .= "ID_MOR_ZONA,";
   $query .= "NOME, ";  
   $query .= "COD_ZONA,";
   $query .= "DATA_INIZIO_VAL,";
   $query .= "DATA_FINE_VAL) ";
   $query .= "VALUES (";
   $query .= "'MODIFICA',";
   $query .= "$row[id],";
   $query .= "$row[id_mor_zona],";
   $query .= "'$row[nome]',";
   $query .= "'$row[cod_zona]',";
   $query .= "'$row[data_inizio_val]',";
   $query .= "'$dataOggi')";

   //echo $query;

   $result = $conn->query($query);

   if (!$result)
        throw new Exception($conn->error);

    $query=  "UPDATE morance SET ";
    $query.= "NOME='$nome', "  ; 
    $query.= "COD_ZONA='$cod_zona', "  ; 
    $query.= "ID_OSM=$id_osm "  ;
    $query.=" WHERE ID=$id_moranca "; 

   //echo $query;

   $result = $conn->query($query);
   if (!$result) 
        throw new Exception($conn->error);
 
   $conn->commit(); 
   $conn->autocommit(TRUE);
   $conn->close();
  }
 catch ( Exception $e )
  {
    $conn->rollback(); 
    $conn->autocommit(TRUE); // i.e., end transaction
	$conn->close();
    $mymsg =  "Errore nella modifica  della moranca: ";
	$mymsg .=  $conn->error; 
	EchoMessage($mymsg, "gest_morance.php?pag=$pag");
  }
 
 EchoMessage("Modifica moranca effettuata correttamente", "gest_morance.php?pag=$pag");
  
?>

