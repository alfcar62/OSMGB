<?php
/*
*** delete_moranca.php
*** cancellazione moranca, 
*** nella form attiva  del_moranca.php
*** 01/03/2020: Gobbi, Arneodo:  Correzione problema durante l'eliminazione delle morançe
*** 20/02/2020: A. Carlone
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
setup();
isLogged("gestore");
$pag=$_SESSION['pag_m']['pag_m'];
unset($_SESSION['pag_m']);
?>
<?php

if (isset($_POST['si']) && $_POST['si'] =='si')
{
$id_moranca=$_POST["id_moranca"];
$dataOggi=date("Y/m/d");
try 
 {
   $conn->query("START TRANSACTION"); //inizio transazione

   $query = "SELECT count(ID) FROM casa WHERE ID_MORANCA='$id_moranca' ";
   $result = $conn->query($query);
   $row = $result->fetch_array();
   if ($row[0] >0)
  	 EchoMessage("Impossibile cancellare: verificare se vi sono case presenti", "gest_morance.php?pag=$pag");
   else
    {
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
     $query .= "'DEL',";
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

     $query="DELETE FROM morance  where ID=$id_moranca ";
   
   //echo $query;

     $result = $conn->query($query);
     if (!$result) 
        throw new Exception($conn->error);
 
     $conn->commit(); 
     $conn->autocommit(TRUE);
     $conn->close();
    }
   }
 catch ( Exception $e )
  {
    $conn->rollback(); 
    $conn->autocommit(TRUE); // i.e., end transaction
	$conn->close();
    $mymsg =  "Errore nella Cancellazione della moranca:";
	EchoMessage($mymsg, "gest_morance.php?pag=$pag");
  }

 EchoMessage("Cancellazione moranca effettuata correttamente", "gest_morance.php?pag=$pag");
}
else
 header("Location:gest_morance.php?pag=$pag");
?>