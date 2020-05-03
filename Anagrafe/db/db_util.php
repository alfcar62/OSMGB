<?php

/*
*** db_util.php : funzioni di utilità sul database
*/


/*
*** ins_log_utente: effettua l'inserimento sulla tabella log_utente
*** input: user
*** return: -1 errore
***          0 OK
*/
function ins_log_utente($utente)
{
 include ("db_conn.php");

 $query = "INSERT INTO log_utente (utente, data) VALUES ";
 $query .= "('" . $utente . "',";
 $query .= "now())";

	
 echo "<br> query: ". $query . "<br><br>";

 $result = mysqli_query($conn, $query);

 if ($result != 0)
  {
	    echo "Errore istruzione SQL\n";
		echo  $query;
		return -1;
  }

 // close connection 
 mysqli_close($conn);

 return 0;
}

/*
*** del_moranca: effettua la cancellazione  (logica) della moranca
*** input: id_moranca
*** return: -1 errore
***          0 OK
*/
function del_moranca($id_moranca)
{
 include ("db_conn.php");

 $dataOggi=date("Y/m/d"); 

 $query="UPDATE morance set DATA_FINE_VAL='$dataOggi' where ID='$id_moranca' ";
	
 echo "<br> query: ". $query . "<br><br>";

 $result = mysqli_query($conn, $query);

 if ($result != 0)
  {
	    echo "del_moranca(): Errore istruzione SQL\n";
		echo  $query;
		return -1;
  }

 // close connection 
 mysqli_close($conn);

 return 0;
}


/*
*** get_tipo_utente: ritorna la tipologia dell'utente 
*** input: username
*** return:  -1  errore (utente non trovato)
*** return:  1 "admin" 
*** return:  2 "gestore" 
*** return:  3 "utente" 
*/
function get_tipo_utente($utente)
{
 include ("db_conn.php");

 $query = "SELECT id_accesso from utenti where user = $utente ";

	
 echo "<br> query: ". $query . "<br><br>";

 $result = mysqli_query($conn, $query);

 if ($result != "OK")
  {
	    echo "get_tipo_utente(): Errore istruzione SQL\n";
		echo  $query;
		mysqli_close($conn);
		return KO;
  }
 
 $row= $result->fetch_array();
  
 if ($row[0] == "admin")
    $res = 1;
 if ($row[0] == "gestore")
    $res = 2;
 if ($row[0] == "utente")
    $res = 3;
 else
   {
     echo "get_tipo_utente():Errore tipo utente sconosciuto\n";
     $res = -1;
   }
 mysqli_close($conn);
 return $res;
}

/*
*** ritorna le informazioni della casa passata in input
*** input: id_casa
*** output: $nome_casa, $nome_moranca, $id_osm, $nome_zona, $capo_famiglia, $num_persone
*** ret_code:  0 = OK
***           -1 = KO
*/
function casa_get_info($id_casa,&$nome_casa, &$nome_moranca, &$id_osm, &$nome_zona, &$capo_famiglia, &$num_persone)
{
 include ("db_conn.php");

  $query = "SELECT m.nome as nome_moranca, z.nome as zona, c.id as id_casa, c.id_osm, c.nome as nome_casa,";
  $query .= "p.nominativo as capo_famiglia";
  $query .= " FROM morance m INNER JOIN casa c ON m.id = c.id_moranca ";
  $query .= " INNER JOIN zone z  ON  z.cod = m.cod_zona ";
  $query .= "LEFT JOIN pers_casa pc ON c.id  = pc.id_casa ";
  $query .=" AND pc.cod_ruolo_pers_fam = 'CF'";
  $query .=" LEFT JOIN persone p ON p.id = pc.id_pers";
  $query .= " WHERE c.id =". $id_casa;
  $result = $conn->query($query);  
  if (!$result)
	{
      echo 'casa_get_info():Errore istruzione SQL\n';
	  mysqli_close($conn);
      return -1;
   }
  $nr=$result->num_rows;
  
  $row = $result->fetch_array();

  $nome_zona = $row['zona'];
  $nome_casa = $row['nome_casa'];
  $id_osm = $row['id_osm'];
  $capo_famiglia = utf8_encode ($row['capo_famiglia']) ;
  if ($capo_famiglia=="")
        $capo_famiglia = "Non Esiste";
  $nome_moranca = utf8_encode ($row['nome_moranca']) ;
  
  $query2="SELECT COUNT(pers_casa.id_pers) as persone from pers_casa WHERE id_casa=".$id_casa;
  $result2 = $conn->query($query2);
  $row2 = $result2->fetch_array();
  $num_persone = $row2['persone'];
  return 0;
  }
?>