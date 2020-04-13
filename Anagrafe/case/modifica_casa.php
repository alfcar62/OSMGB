<?php
/*
*** modifica_casa.php
*** Viene richiamato da mod_casa.php (submit form)
*** input: POST(id_casa, moranca,id_osm)
*** effettua le modifiche che arrivano da modifica_casa.php
*** ed inserisce sulla tabella storica  "casa_sto" il vecchio record
*** 14/3/2020: A.Carlone: modifiche varie
*** 11/3/2020: Ferraiuolo, Arneodo
*/

$config_path = __DIR__;
//$util1 = "E:/xampp/htdocs/OSM/Anagrafe/util.php";
$util1="../util.php";
//$util2 = "E:/xampp/htdocs/OSM/Anagrafe/db/db_conn.php";
 $util2="../db/db_conn.php";
require_once $util2;
require_once $util1;
setup();
$pag=$_SESSION['pag_c']['pag_c'];
unset($_SESSION['pag_p']);
/*
***i nuovi valori da impostare
*/
$id_casa         =$_POST["id_casa"];
$id_moranca_new  =$_POST["moranca"];
$id_osm_new      =$_POST["id_osm"];
$id_osm_new      =stripslashes($id_osm_new);						//protezione da sql injection
$id_osm_new      = mysqli_real_escape_string($conn,$id_osm_new);	//protezione da sql injection

$nome_casa_new  = $_POST["nome_casa"];
$nome_casa_new  = stripslashes($nome_casa_new);//protezione da sql injection
$nome_casa_new  = mysqli_real_escape_string($conn,$nome_casa_new);//protezione da sql injection

$dataInizio = $_POST["data_inizio"];
$dataFine   = $_POST["data_fine"];
if($id_osm_new == '')
    $id_osm_new =0;
try 
 {
   $conn->query("START TRANSACTION"); //inizio transazione

   /*
    *** verifica se la casa ha un capo famiglia
   */

   $query =  "SELECT p.id as id_pers, p.nominativo as capo_famiglia ";
   $query .= " FROM persone p INNER JOIN pers_casa pc  ON p.id = pc.id_pers ";
   $query .= " INNER JOIN  casa c ON c.id = pc.id_casa ";
   $query .= " WHERE c.id = $id_casa";
   $query .="  AND pc.cod_ruolo_pers_fam = 'CF'";
//   echo "q1: ". $query. "<br>";
   $result = $conn->query($query);
   if (!$result)
        throw new Exception($conn->error);
   $row = $result->fetch_array();
   if ($result->num_rows ==0)
	{
	   $capo_famiglia = "";
    }
   else 
	   $capo_famiglia = $row['capo_famiglia'];

  /*
  *** recupera i dati della casa
  */
   $query =  "SELECT c.nome as nome_casa,";
   $query .= "c.id_osm as id_osm, ";
   $query .= "c.data_inizio_val as data_inizio, c.data_fine_val as data_fine,";
   $query .= "c.id_moranca, m.nome as nome_moranca ";
   $query .= " FROM casa c INNER JOIN morance m ON c.id_moranca = m.id";
   $query .= " WHERE c.id =". $id_casa;
   $query .= " AND c.data_fine_val is null";
//   echo "q2: ". $query. "<br>";

   $result = $conn->query($query);
   if (!$result)
      throw new Exception($conn->error);

   $row = $result->fetch_array(); 

   $data_attuale = date('Y/m/d');
  
   $dataFine   = $_POST["data_fine"];

   $id_osm = $row['id_osm'];
   if($id_osm == '')
     $id_osm =0;

  if (($nome_casa_new !=  $row['nome_casa']) ||
      ($id_moranca_new != $row['id_moranca']) ||
      ($id_osm_new != $id_osm))
     $upd = true;
  else
     $upd = false;
  
   if ($upd)
   { 
  /* 
	*** Insert su "casa_sto"
	*** sullo storico "casa_sto" teniamo traccia dei cambiamenti di una casa.
	*** Nota: la modifica del capo famiglia può essere fatta con la modifica persona e
	*** verrà storicizzata su "persone_sto"
    *** Possono cambiare: nome, moranca, id_osm
  */
   $tipo_operazione= "MOD (";
   if ($nome_casa_new!=$row['nome_casa'] ||
	   $id_moranca_new!=$row['id_moranca'] || 
	   $id_osm_new!=$row['id_osm'])
	 {
      if ($nome_casa_new != $row['nome_casa'])
         $tipo_operazione.="-NOME-";
      
	  if ($id_moranca_new !=$row['id_moranca'])
        $tipo_operazione.="-MORANCA-";
      
      if ($id_osm_new !=$id_osm)
        $tipo_operazione.="-ID OSM-";
      $tipo_operazione.=")";
  
      $query= " INSERT INTO casa_sto (";
      $query .= " TIPO_OP, ";
      $query .= " ID_CASA,";
      $query .= " NOME,  ";
      $query .= " ID_MORANCA,";
      $query .= " NOME_MORANCA,";
      $query .= " ID_OSM,";
      $query .= " NOME_CAPO_FAMIGLIA,";
      $query .= " DATA_INIZIO_VAL,";
      $query .= " DATA_FINE_VAL)";
      $query .= " VALUES (";
      $query .= "'".$tipo_operazione."',";
      $query .= $id_casa.",";  
      $query .= "'".$row['nome_casa']."',";
      $query .= $row['id_moranca'].",";
      $query .= "'".$row['nome_moranca']."',";
      $query .= $id_osm.",";
      $query .= "'".$capo_famiglia."',";	//capo famiglia
      $query .= "'".$row['data_inizio']."',";
      $query .= "'".$data_attuale."')";		//data fine val
      $result = $conn->query($query);
    // echo "q3:". $query . "<br>";
      if (!$result)
        throw new Exception($conn->error);
     }


  /*
   *** UPDATE su "casa"
   */
   $query=   "UPDATE casa " ;
   $query .= "SET casa.nome = '". $nome_casa_new."',";
   $query .= "id_moranca   = ". $id_moranca_new.",";
   $query .= "id_osm       = ". $id_osm_new.",";
   $query .= "data_inizio_val='".$data_attuale."'";
   $query .= " WHERE casa.id = ".$id_casa;
   //echo "q4 ".$query;
   $result = $conn->query($query); 
   if (!$result)
      throw new Exception($conn->error);

	$conn->commit();
	$conn->autocommit(TRUE);
    $conn->close();
   }// upd
  } //try
 catch ( Exception $e )
  {
	echo $conn->error;
    $conn->rollback(); 
    $conn->autocommit(TRUE); // i.e., end transaction
	$conn->close();
    $mymsg = "Errore modifica  casa" . $conn->error;
    EchoMessage($mymsg, "gest_case.php?pag=$pag");
  }
   $mymsg = "Modifica casa effettuata correttamente";
   EchoMessage($mymsg, "gest_case.php?pag=$pag");
?>