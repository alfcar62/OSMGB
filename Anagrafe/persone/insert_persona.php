<?php 
/*
***14/3/2020: A.Carlone: correzioni varie, aggiunta gestione transazione
***1/03/2020  Gobbi Dennis Alessandro Arneodo: bug fox su insert
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
setup();
isLogged("utente");
$nominativo=$_POST["nome_persona"];
$data_nascita=$_POST["data_nascita"];
$id_casa=$_POST["id_casa_nuova"];
$cod_ruolo=$_POST["id_ruolo_nuovo"];
$sesso=$_POST["sesso"];
$data_odierna = date("y/m/d");

try 
 {
   $conn->query("START TRANSACTION"); //inizio transazione

   $query="select  max(id) as max_id_pers from persone";
   $result=$conn->query($query);
   $row=$result->fetch_array();
   $id_pers=$row["max_id_pers"]+1;
   $result->free();

   $query="INSERT INTO persone (id, nominativo, sesso, data_nascita,data_inizio_val) values ($id_pers,'$nominativo','$sesso','$data_nascita','$data_odierna')";
   $result = $conn->query($query);
  // echo $query."<br>";

   $query2="INSERT INTO pers_casa (id_pers, id_casa, cod_ruolo_pers_fam, data_inizio_val)";
   $query2 .= " VALUES ($id_pers, $id_casa, '$cod_ruolo', '$data_odierna')";
   $result = $conn->query($query2);
  // echo $query2."<br>";
   if (!$result)
     {
        $msg_err = "Errore insert pers_casa";
        throw new Exception($conn->error);
     }
	$conn->commit(); 
    $conn->autocommit(TRUE); // end transaction
	$conn->close();
  }
  catch (Exception $e )
   {
     $conn->rollback(); 
     $conn->autocommit(TRUE);	// end transaction
	 $conn->close();
     echo "Errore in inserimento della persona";
	 echo $conn->error; 
	 echo "transazione con rollback";
	 $mymsg = "Errore inserimento persona id=$id_pers " . $msg_err;
     EchoMessage($mymsg, "gest_persone.php");
   }
   EchoMessage("Inserimento  persona id=$id_pers effettuato correttamnete", "gest_persone.php");
?>
