<?php
/*
*** delete_casa.php
*** Richiamata da gest_case.php
*** esegue la cancellazione dei dati di una casa
*** con gestione dello storico (su tabella "casa_sto")
*** 14/3/2020: A.Carlone: modifiche varie
*** 01/3/2020: Ferraiuolo: aggiunta delle transazioni
*/
$config_path = __DIR__;
//$util1 = "E:/xampp/htdocs/OSM/Anagrafe/util.php";
$util1="../util.php";
//$util2 = "E:/xampp/htdocs/OSM/Anagrafe/db/db_conn.php";
 $util2="../db/db_conn.php";
require_once $util2;
require_once $util1;
setup();
isLogged("utente");
$pag=$_SESSION['pag_c']['pag_c'];
unset($_SESSION['pag_c']);

if (!isset($_POST['si']))
   header("Location:gest_case.php?pag=$pag");

$id_casa=$_POST["id_casa"];

try 
 {
  /*
  *** verifica esistenza di id_casa su pers_casa
  */
   $query="SELECT count(id) as cont from pers_casa where id_casa=". $id_casa;
   $result=$conn->query($query);
 //  echo $query;
   $row = $result->fetch_array();
   $num = $row['cont'];
   if ($num >0)
	 EchoMessage("Impossibile cancellare: verificare se vi sono persone presenti", "gest_case.php");

   /*
   *** recupero dei dati da inserire nello storico casa_sto
   */
   $query =  "SELECT c.nome as nome_casa, c.id_osm as id_osm,";
   $query .= " c.id_moranca as id_moranca, m.nome as nome_moranca, c.data_inizio_val as data_inizio_val";
   $query .= " FROM casa c";
   $query .= " INNER JOIN morance m  ON  c.id_moranca = m.id ";
   $query .= " WHERE c.id = $id_casa ";

 //  echo "q1: ". $query. "<br>";
   $result = $conn->query($query);
    if (!$result)
        throw new Exception($conn->error);
   $row = $result->fetch_array();
   
   /*
   *** salvo i valori da inserire su casa_sto
   */
    $nome_casa =  $row['nome_casa'];

    $id_osm  = $row['id_osm'];
	if ($id_osm == '')
		$id_osm =0;
    $id_moranca = $row['id_moranca'];
    $nome_moranca = $row['nome_moranca'];
    $data_inizio_val = $row['data_inizio_val'];
 

 /*
 *** verifica se la casa ha un capo famiglia
 */

   $query =  "SELECT p.id as id_pers, p.nominativo as capo_famiglia ";
   $query .= " FROM persone p INNER JOIN pers_casa pc  ON p.id = pc.id_pers ";
   $query .= " INNER JOIN  casa c ON c.id = pc.id_casa ";   
   $query .= " WHERE c.id = $id_casa";
   $query .="  AND pc.cod_ruolo_pers_fam = 'CF'";

//   echo "q: ". $query. "<br>";
   $result = $conn->query($query);
   if (!$result)
        throw new Exception($conn->error);
   $row = $result->fetch_array();

   if ($result->num_rows >0)
	{
	  $capo_famiglia = $row['capo_famiglia'];
    }
   else 
	  $capo_famiglia = "";


   $conn->query("START TRANSACTION");

   /*
   *** cancellazione su tabella "casa"
   */
   $query2="DELETE FROM casa  WHERE id=".$id_casa;   
   $result = $conn->query($query2);
   if (!$result)
    {
     $msg_err = "Errore delete casa";
     throw new Exception($conn->error);
    }

   /*
   *** cancellazione su tabella "pers_casa"
   */
   $query2="DELETE FROM pers_casa WHERE id = ".$id_casa;
   $conn->query($query2);
   if (!$result)
    {
     $msg_err = "Errore delete pers_casa";
     throw new Exception($conn->error);
    }
     
   /*
   ***  inserimento su casa_sto
   */
      $currentdate = date('Y/m/d');
      $tipo_op = "DELETE";

      $query2= " INSERT INTO casa_sto (";
      $query2 .= " TIPO_OP, ";
	  $query2 .= " ID_CASA,";
	  $query2 .= " NOME,  ";
	  $query2 .= " ID_MORANCA,";
	  $query2 .= " NOME_MORANCA,";
	  $query2 .= " ID_OSM,";
	  $query2 .= " NOME_CAPO_FAMIGLIA,";
	  $query2 .= " DATA_INIZIO_VAL,";
	  $query2 .= " DATA_FINE_VAL)";
	  $query2 .= " VALUES (";
      $query2 .= "'$tipo_op',";
	  $query2 .=  $id_casa.",";  
	  $query2 .= "'".$nome_casa."',";
	  $query2 .= $id_moranca.",";
	  $query2 .= "'".$nome_moranca."',";
	  $query2 .= $id_osm.",";
	  $query2 .= "'".$capo_famiglia."',";
	  $query2 .= "'".$data_inizio_val."',";
	  $query2 .= "'".$currentdate."')";			//data fine val
      $result = $conn->query($query2);
//	  echo "q2:". $query2 . "<br>";
	  if (!$result)
       {
        $msg_err = "Errore insert casa_sto";
        throw new Exception($conn->error);
       }
    $conn->commit();
	$conn->autocommit(TRUE);
    $conn->close();
  } //try
catch ( Exception $e )
  {
    $conn->rollback(); 
    $conn->autocommit(TRUE); // i.e., end transaction
	$conn->close();

    $mymsg = "Errore cancellazione casa id=" . $id_casa . "err:" . $msg_err;
    EchoMessage($mymsg, "gest_case.php?pag=$pag");
   }
  EchoMessage("Cancellazione casa effettuata correttamente", "gest_case.php?pag=$pag");
?>