<?php
/*
*** mod_persona.php
*** 14/3/2020: A.Carlone: correzioni varie, per la gestione dello storico
*** 03/03/2020  Autore:Gobbi Dennis
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
setup();
isLogged("gestore");
if (isset($_SESSION['pag_p']['pag_p']))
   $pag=$_SESSION['pag_p']['pag_p'];
unset($_SESSION['pag_c']);

// salvo i nuovi valori
$nominativo_new=$_POST['nominativo'];
//echo "nominativo nuovo:$nominativo_new<br>";

$id_ruolo_modifica_new=$_POST['id_ruolo_modifica'];//ruolo pers modifica
//echo "ruolo:$id_ruolo_modifica_new<br>";

$data_nascita_new=($_POST['data_nascita'] != '')? $_POST['data_nascita']:"0000-00-00";
//echo "nascita:$data_nascita_new<br>";

$data_morte_new=($_POST['data_morte'] != '') ? $_POST['data_morte']:"0000-00-00";
//echo "morte:$data_morte_new<br>";

$id_pers_modifica=$_SESSION['id_persona_modifica'];
//echo "id_pers:$id_pers_modifica<br>";

$id_casa_new=$_POST['id_casa_modifica'];//casa pers modifica
//echo "id_casa:$id_casa_new<br>";

try 
 {
 // $conn->query("START TRANSACTION"); //inizio transazione

  //query per prendere i valori della persona pre-modifica

  $query  =  " SELECT p.nominativo,";
  $query .=  " p.data_nascita,";
  $query .=  " p.data_morte,";
  $query .=  " p.data_inizio_val,";
  $query .=  " p.sesso,";
  $query .=  " c.id as id_casa,";
  $query .=  " c.nome as nome_casa,";
  $query .=  " pc.cod_ruolo_pers_fam as cod_ruolo,";
  $query .=  " rpf.descrizione as desc_ruolo";
  $query .=  " FROM persone p, pers_casa pc, casa c, ruolo_pers_fam rpf";
  $query .= " WHERE p.id =$id_pers_modifica";
  $query .= " AND pc.id_pers = p.id";
  $query .= " AND c.id = pc.id_casa";
  $query .= " AND pc.cod_ruolo_pers_fam = rpf.cod";

 // echo "q1 ".$query."<br>";
  $result=$conn->query($query);
  if (!$result)
   {
     $msg_err = "Errore select n.1";
     throw new Exception($conn->error);
   }
  $row=$result->fetch_array();

  $tipo_operazione="MOD (";
  $casa_cambiata=false;
  $ruolo_cambiato=false;
  $nominativo_cambiato = false;
  $data_nascita_cambiata = false;
  $data_morte_cambiata = false;

  $sesso_old =$row['sesso']; 
  $nome_casa_old =$row['nome_casa']; 
  $desc_ruolo_old =$row['desc_ruolo']; 
  $nominativo_old =$row['nominativo'];
  $cod_ruolo_old = $row['cod_ruolo']; 
  $data_nascita_old =  $row['data_nascita'];
  $data_morte_old =  $row['data_morte'];
  $id_casa_old = $row['id_casa'];

  if($nominativo_new != $row['nominativo'])
   {
    $tipo_operazione.="-nominativo-";
    $nominativo_cambiato=true; 
   }
  
  if($id_ruolo_modifica_new != $row['cod_ruolo'])
   {
    $tipo_operazione.="-ruolo-";
    $ruolo_cambiato=true;
//	echo $row['cod_ruolo'];
//	echo "ruolo cambiato";
   }
  
  $data_nascita =($row['data_nascita'] != '') ? $row['data_nascita']:"0000-00-00";

  if($data_nascita_new != $data_nascita)
   {
    $tipo_operazione.="-data_nascita-";
    $data_nascita_cambiata=true;
   }

   $data_morte =($row['data_morte'] != '') ? $row['data_morte']:"0000-00-00";

   if($data_morte_new != $data_morte)
   {
    $tipo_operazione.="-data_morte-";
    $data_morte_cambiata=true;
   }


  if( $id_casa_new != 's' && $id_casa_new != $row['id_casa'])
   {
    $tipo_operazione.="-casa-";
    $casa_cambiata=true;
   }
      
  $tipo_operazione.=")";

  $data_inizio_val=$row['data_inizio_val'];
  $currentdate=date('Y/m/d');


  // se la nuova casa ha già un capo famiglia, non posso scegliere come ruolo capo famiglia
  if ($ruolo_cambiato  && $id_ruolo_modifica_new == 'CF')
   {
    $query  =  " SELECT count(pc.id) as cont FROM casa c, pers_casa pc ";
    $query .=  " WHERE pc.id_casa = c.id ";
    $query .=  " AND c.id =". $id_casa_new;
    $query .=  " AND pc.cod_ruolo_pers_fam = 'CF'";
//	echo $query;

    $result = $conn->query($query);

	if (!$result)
     {
      $msg_err = "Errore select n.2";
      throw new Exception($conn->error);
     }
    $row = $result->fetch_array();
    if ($row['cont']>0) 
	 {
       $msg_err = "Esiste un capo famiglia: selezionare altro ruolo";
       throw new Exception($msg_err);
     }
   }

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
 $query .= "'".$nominativo_old."',";
 $query .= "'".$sesso_old."',";

 if ($data_nascita == "0000-00-00")
    $query .= "NULL,";
 else
    $query .= "'".$data_nascita."',";

 if ($data_morte == "0000-00-00")
    $query .= "NULL,";
 else
    $query .= "'".$data_morte."',";

 $query .= $id_casa_old .",";
 $query .= "'".$nome_casa_old."',";
 $query .= "'".$cod_ruolo_old."',";
 $query .= "'".$desc_ruolo_old."',";
 $query .= "'$data_inizio_val',";
 $query .= "'$currentdate')";
 
// echo "q2 ".$query."<br>";
 $result = $conn->query($query);

 if (!$result)
  {
     $msg_err = "Errore insert persone_sto";
     throw new Exception($conn->error);
  }

 $upd_pers      = false;
 $upd_pers_casa = false;

 if ($nominativo_cambiato || $data_nascita_cambiata || $data_morte_cambiata)
       $upd_pers=true;

 if ($casa_cambiata || $ruolo_cambiato)
      $upd_pers_casa = true;
   

 if($upd_pers)
   {
    /*
    *** UPDATE persone
    */
    $query= "UPDATE persone SET ";
    $query .= "nominativo="."'".$nominativo_new."'";

	if ($data_nascita_new == "0000-00-00")
        $query .= ",data_nascita = NULL ";
	else
        $query .= ",data_nascita = '". $data_nascita_new . "'";

    if ($data_morte_new == "0000-00-00")
        $query .= ",data_morte = NULL ";
	else
        $query .= ",data_morte= '". $data_morte_new . "' ";

    $query .= " where id= ".$id_pers_modifica;
	
//	echo "q3 ".$query."<br>";

    $result = $conn->query($query);
    if (!$result)
     {
      $msg_err = "Errore update persone";
      throw new Exception($conn->error);
     }
    }
    /*
    *** UPDATE pers_casa
    */
	if ($upd_pers_casa)
     {
      $query="UPDATE pers_casa ";
      $query=$query." SET cod_ruolo_pers_fam="."'".$id_ruolo_modifica_new. "'";
      $query=$query.", id_casa=".$id_casa_new;
	  $query=$query." WHERE pers_casa.id_pers=".$id_pers_modifica;
   
//      echo "q4 ".$query."<br>";;
      $result = $conn->query($query);
      if (!$result)
       {
        $msg_err = "Errore update pers_casa";
        throw new Exception($conn->error);
       }
     }
    $conn->commit();
	$conn->autocommit(TRUE);
    $conn->close();
  }//try
 catch ( Exception $e )
  {
	$conn->rollback(); 
    $conn->autocommit(TRUE); // i.e., end transaction
//	echo $conn->error; 
	$conn->close();
//	echo $msg_err;
    echo "Errore nella modifica della persona";
	echo "transazione con rollback";
    $mymsg = "Modifica persona id=$id_pers_modifica " . $msg_err;
    EchoMessage($mymsg, "gest_persone.php?pag=$pag");
  }
 EchoMessage("Modifica persona id=$id_pers_modifica effettuata correttamente", "gest_persone.php?pag=$pag");
?>




