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
$pag=$_SESSION['pag_p']['pag_p'];
unset($_SESSION['pag_p']);

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

  $tipo_operazione="MODIFICATO (";
  $casa_cambiata=false;
  $ruolo_cambiato=false;
  $nominativo_cambiato = false;
  $data_nascita_cambiata = false;
  $data_morte_cambiata = false;

  if($nominativo_new!=$row['nominativo'])
   {
    $tipo_operazione.="nominativo ";
    $nominativo_cambiato=true;
   }
  
  if($id_ruolo_modifica_new != 's' && $id_ruolo_modifica_new != $row['cod_ruolo'])
   {
    $tipo_operazione.="ruolo ";
    $ruolo_cambiato=true;
   }
  
  $data_nascita =($row['data_nascita'] != '') ? $row['data_nascita']:"0000-00-00";

  if($data_nascita_new != $data_nascita)
   {
    $tipo_operazione.="data_nascita";
    $data_nascita_cambiata=true;
   }

   $data_morte =($row['data_morte'] != '') ? $row['data_morte']:"0000-00-00";

   if($data_morte_new != $data_morte)
   {
    $tipo_operazione.="data_morte";
    $data_morte_cambiata=true;
   }


  if( $id_casa_new != 's' && $id_casa_new != $row['id_casa'])
   {
    $tipo_operazione.="casa ";
    $casa_cambiata=true;
   }
      
  $tipo_operazione.=")";

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
 $query .= "'".$data_nascita."',";
 $query .= "'".$data_morte."',";
 $query .= $row['id_casa'].",";
 $query .= "'".$row['nome_casa']."',";
 $query .= "'".$row['cod_ruolo']."',";
 $query .= "'".$row['desc_ruolo']."',";
 $query .= "'$data_inizio_val',";
 $query .= "'$currentdate')";
 
 //echo "q2 ".$query."<br>";
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
    $query="UPDATE persone SET ";
    $query=$query."nominativo="."'".$nominativo_new."'";
    $query=$query.", data_morte= "."'".$data_morte_new."'";  
    $query=$query.", data_nascita= "."'".$data_nascita_new."'";
    $query=$query." where id= ".$id_pers_modifica;
	
	//echo "q3 ".$query."<br>";

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
   
     // echo "q4 ".$query."<br>";;
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
	$conn->close();
	echo $msg_err;
    echo "Errore nella modifica della persona";
	echo $conn->error; 
	echo "transazione con rollback";
    $mymsg = "Errore nella modifica persona id=$id_pers_modifica " . $msg_err;
    EchoMessage($mymsg, "gest_persone.php?pag=$pag");
  }
 EchoMessage("Modifica persona id=$id_pers_modifica effettuata correttamente", "gest_persone.php?pag=$pag");
?>




