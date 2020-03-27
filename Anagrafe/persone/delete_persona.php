<?php
/*
*** delete_persona.php
*** Richiamata da del_persona.php
*** effettua la cancellazione delle persona e la storicizza
*** 14/3/2020: A.Carlone: modifiche varie
*** 01/3/2020: Gobbi, Arneodo: aggiunta delle transazioni
*** Bug fix sull'update,inserimento modifica nella tabella storico "persone_sto"
*/

$config_path = __DIR__;
//$util1 = "E:/xampp/htdocs/OSM/Anagrafe/util.php";
$util1="../util.php";
//$util2 = "E:/xampp/htdocs/OSM/Anagrafe/db/db_conn.php";
 $util2="../db/db_conn.php";
require_once $util2;
require_once $util1;
setup();
$pag=$_SESSION['pag_p']['pag_p'];
unset($_SESSION['pag_p']);

$id_pers_modifica=$_POST["id_pers"];

if (!isset($_POST['si']))
  header("Location:gest_persone.php?pag=$pag");
	 
try 
 {
  $conn->query("START TRANSACTION"); //inizio transazione
  
  //query per prendere i valori della persona pre-modifica

  $query  =  " SELECT p.nominativo,";
  $query .=  " p.data_nascita,";
  $query .=  " p.data_morte,";
  $query .=  " p.data_inizio_val,";
  $query .=  " p.sesso,";
  $query .=  " c.id as id_casa,";
  $query .=  " c.nome as nome_casa, c.id_osm as id_osm,";
  $query .=  " m.id as  id_moranca,";
  $query .=  " m.nome as nome_moranca,";
  $query .=  " pc.cod_ruolo_pers_fam as cod_ruolo,";
  $query .=  " rpf.descrizione as desc_ruolo";
  $query .=  " FROM persone p, pers_casa pc, casa c,morance m, ruolo_pers_fam rpf";
  $query .= " WHERE p.id =$id_pers_modifica";
  $query .= " AND pc.id_pers = p.id";
  $query .= " AND c.id = pc.id_casa";
  $query .= " AND m.id = c.id_moranca";
  $query .= " AND pc.cod_ruolo_pers_fam = rpf.cod";

//  echo "query: ".$query."<br>";
  $result=$conn->query($query);
  if (!$result)
   {
    $msg_err = "Errore insert persone_sto";
    throw new Exception($conn->error);
   }
  $row=$result->fetch_array();
 
  $dataOggi=date("Y/m/d");
 
  $conn->query("START TRANSACTION"); //inizio transazione

  $tipo_operazione = "DELETE (persona)";
  $data_inizio_val=$row['data_inizio_val'];
  $currentdate=date('Y/m/d'); 
/*
*** INSERT su persone_sto (vecchi valori)
*/
 $query="INSERT INTO persone_sto (";
 $query .= "  TIPO_OP, ";
 $query .= "  ID_PERSONA, ";
 $query .= "  NOMINATIVO, ";
 $query .= "  SESSO, ";
 $query .= "  DATA_NASCITA, DATA_MORTE,";
 $query .= "  ID_CASA, NOME_CASA,";
 $query .= "  COD_RUOLO_PERS_FAM, DESC_RUOLO_PERS_FAM,";
 $query .= "  DATA_INIZIO_VAL,DATA_FINE_VAL ";
 $query .= "  )";
 $query .= " VALUES(";
 $query .= "'$tipo_operazione',";
 $query .= $id_pers_modifica.",";
 $query .= "'".$row['nominativo']."',";
 $query .= "'".$row['sesso']."',";
 $query .= "'".$row['data_nascita']."',";
 $query .= "'".$row['data_morte']."',";
 $query .= "'".$row['id_casa']."',";
 $query .= "'".$row['nome_casa']."',";
 $query .= "'".$row['cod_ruolo']."',";
 $query .= "'".$row['desc_ruolo']."',";
 $query .= "'$data_inizio_val',";
 $query .= "'$currentdate');";

 $result = $conn->query($query);
 if (!$result)
   {
    $msg_err = "Errore insert persone_sto";
    throw new Exception($conn->error);
   }
 
 /*
  *** inserimento su storico case
 */
 $currentdate = date('Y/m/d');
 $tipo_op = "DELETE (persona)";
 $capo_famiglia = " XXX "; // per ora: determinare il capo famiglia della casa
 $nome_persona = $row['nominativo'];  //nominativo della persona che ha cambiato casa

 $query2= " INSERT INTO casa_sto (";
 $query2 .= " TIPO_OP, ";
 $query2 .= " ID_CASA,";
 $query2 .= " NOME,  ";
 $query2 .= " ID_MORANCA,";
 $query2 .= " NOME_MORANCA,";
 $query2 .= " ID_OSM,";
 $query2 .= " NOME_CAPO_FAMIGLIA,";
 $query2 .= " NOME_PERSONA,";
 $query2 .= " DATA_INIZIO_VAL,";
 $query2 .= " DATA_FINE_VAL)";
 $query2 .= " VALUES (";
 $query2 .= "'$tipo_op',";
 $query2 .=  $row['id_casa'].",";  
 $query2 .= "'".$row['nome_casa']."',";
 $query2 .= $row['id_moranca'].",";
 $query2 .= "'".$row['nome_moranca']."',";
 $query2 .= $row['id_osm'].",";
 $query2 .= "'".$capo_famiglia."',";
 $query2 .= "'".$nome_persona."',";
 $query2 .= "'".$data_inizio_val."',"; 
 $query2 .= "'".$currentdate."')";			//data fine val

 $result = $conn->query($query2);
// echo "q2:". $query2 . "<br>";
 if (!$result)
  {
    $msg_err = "Errore insert casa_sto";
    throw new Exception($conn->error);
  }
 
 /*
 *** Importante cancellare prima su pers_casa e poi su persone (FK!)
 */

 $query2="DELETE FROM pers_casa  WHERE id_pers=".$id_pers_modifica;   
 $result = $conn->query($query2);
 if (!$result)
  {
    $msg_err = "Errore delete pers_casa";
    throw new Exception($conn->error);
  }

 $query3 ="DELETE FROM persone  WHERE id=".$id_pers_modifica;   
 $result = $conn->query($query3);
 if (!$result)
  {
    $msg_err = "Errore delete persone";
    throw new Exception($conn->error);
  }

 $conn->commit(); 
 $conn->autocommit(TRUE);
 $conn->close();
 } //try
 catch (Exception $e)
  {   
    echo "- Errore nella Cancellazione della persona:";
	echo $msg_err .  " ";
	echo $conn->error; 
	echo "transazione con rollback";
	$conn->rollback(); 
    $conn->autocommit(TRUE); // i.e., end transaction
	$conn->close();
	$mymsg = "Errore nella cancellazione della persona id =$id_pers_modifica " . $msg_err;
    EchoMessage($mymsg, "gest_persone.php?=$pag");
  }
 EchoMessage("Cancellazione persona id=$id_pers_modifica effettuata correttamente", "gest_persone.php?=$pag");
?>
 
