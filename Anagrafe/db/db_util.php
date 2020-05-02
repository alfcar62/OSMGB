<?php


/*
***db_util.php : funzioni di utilità sul database mysql
*/

/* definizione di costanti */
define("OK", 0);
define("KO", -1);


/*
*** inserisci_casa: effettua la INSERT su DB di una casa
*** input: id casa
*** return: -1 errore
***         0 OK
*/
function inserisci_casa($id)
{
 include ("db_conn.php");		//connessione al DB
 echo "entro in inserisci_casa() id = ". $id;
 // lettura dal file geojson
 $points = file_get_contents('points.geojson');
 $pointsarray = json_decode($points, true);

 $i = 0;
 foreach ($pointsarray["features"] as $key => $item)
  {
      if ($item["properties"]["name"] == $id)
		  {
            break;
          }
       $i++;
  }

	 $idc = $pointsarray["features"][$i]["properties"]["name"];
	 print '<b>id casa:'.$idc.':</b><br>';
	 
	 $tag = $pointsarray["features"][$i]["properties"]["tag"];
	 print '<b> tag:'.$tag.':</b><br>';

	 $data_val = $pointsarray["features"][$i]["properties"]["verified"];
	 print '<b> data validazione:'.$data_val.':</b><br>';
	
	 $nome = $pointsarray["features"][$i]["properties"]["description"]["Casa"];
	 print '<b>nome casa:'.$nome.':</b><br>';
	 $moranca = $pointsarray["features"][$i]["properties"]["description"]["Moranca"];
	 print '<b> moranca:'.$moranca.':</b><br>';
     $capof = $pointsarray["features"][$i]["properties"]["description"]["Capo Famiglia"];
	 print '<b> capo famiglia:'.$capof.':</b><br>';
	 $numper = $pointsarray["features"][$i]["properties"]["description"]["Numero Persone"];
	 print '<b> numero persone:'.$numper.':</b><br>';
	 $lon = $pointsarray["features"][$i]["geometry"]["coordinates"][0];
	 print '<b> longitude:'.$lon.':</b><br>';
	 $lat = $pointsarray["features"][$i]["geometry"]["coordinates"][1];
	 print '<b> latitude:'.$lat.':</b><br>';
	

	$query = "INSERT INTO casa ";
    $query .= "( id, nome, moranca, capo_famiglia, num_persone, latitude,longitude, tag, data_val) ";
    $query .= " VALUES (";
	$query .= $idc . ",";
	$query .= "'" . $nome . "',";
    $query .= "'" . $moranca . "',";
	$query .= "'" . $capof . "',";
    $query .=  $numper . ",";
    $query .=  $lat . ",";
    $query .=  $lon . ",";
    $query .= "'" . $tag . "',";
	$query .= "STR_TO_DATE('". $data_val ."', '%d/%m/%Y')";
    $query .= ")";
//	echo "<br> query: ".$query . "<br><br>";

	$result = mysqli_query($conn, $query);

	if (!$result)
	  {
	    echo 'Errore istruzione SQL\n';
		echo  $query;
		return -1;
	  }
	$i++;
   
   echo "<br> numero righe inserite su DB=".  mysqli_affected_rows($conn). "<br>";

// close connection 
mysqli_close($conn);
echo "<br>  casa inserita correttamente su DB\n";

 return 0;
}

/*
*** nuova_assoc_casa: effettua la nuova associazione tra casa e id_osm
*** richiamata da edit.php a fronte di un punto sulla mappa  da associare ad una casa presente sul DB
*** input: id casa
*** return: -1 errore
***         0 OK
*/
function nuova_assoc_casa($id)
{
 include ("db_conn.php");		//connessione al DB
 // lettura dal file geojson
 $points = file_get_contents('points.geojson');
 $pointsarray = json_decode($points, true);

 $i = 0;
 foreach ($pointsarray["features"] as $key => $item)
  {
      if ($item["properties"]["name"] == $id)
		  {
            break;
          }
       $i++;
  }

	 $id_casa = $pointsarray["features"][$i]["properties"]["name"];
	 print '<b>id casa:'.$id_casa.':</b><br>';
	 
	 $tag = $pointsarray["features"][$i]["properties"]["tag"];
	 print '<b> tag:'.$tag.':</b><br>';

	 $data_val = $pointsarray["features"][$i]["properties"]["verified"];
	 print '<b> data validazione:'.$data_val.':</b><br>';
	
	 $lon = $pointsarray["features"][$i]["geometry"]["coordinates"][0];
	 print '<b> longitude:'.$lon.':</b><br>';

	 $lat = $pointsarray["features"][$i]["geometry"]["coordinates"][1];
	 print '<b> latitude:'.$lat.':</b><br>';

	 $id_osm_new = $pointsarray["features"][$i]["properties"]["description"]["id OSM"];
	 print '<b>id_osm_new:'.$id_osm_new.':</b><br>';

try 
 {

  /*
  *** recupera i dati della casa
  */
   $query =  "SELECT c.nome as nome_casa,";
   $query .= "c.id_osm as id_osm, c.data_inizio_val ";
   $query .= " FROM casa c ";
   $query .= " WHERE c.id =". $id_casa;
   $query .= " AND c.data_fine_val is null";
//   echo "q2: ". $query. "<br>";

   $result = $conn->query($query);
   if (!$result)
      throw new Exception($conn->error);

   $row = $result->fetch_array(); 
  
 
   $nome_casa = $row['nome_casa'];

    
	$conn->query("START TRANSACTION"); //inizio transazione
   
     $data_attuale = date('Y/m/d');

// inserimento nello storico case

      $tipo_operazione = "(MOD id_OSM da mappa )";
       
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
      $query .= "'".$nome_casa."',";
      $query .= "NULL,";	// id_moranca
      $query .= "NULL,";	// nome_moranca
      $query .= "NULL,";	// id_osm
	  $query .= "NULL,";	// capo famiglia
      $query .= "'".$row['data_inizio_val']."',";  //data inizio val
	  $query .= "'".$data_attuale."')";		//data fine val

      $result = $conn->query($query);
  //  echo "q3:". $query . "<br>";
       
	  if (!$result)
        throw new Exception($conn->error);
     

	$query = "UPDATE casa SET ";
	$query .= "nome = '" . $nome_casa . "',";
	$query .= "id_osm = " . $id_osm_new . ",";
    $query .= "lat = " . $lat . ",";
    $query .= "lon = " . $lon . ",";
    $query .= "data_inizio_val =  STR_TO_DATE('". $data_val ."', '%d/%m/%Y')";
	$query .= " WHERE id = " . $id_casa; 

//	echo "<br> query: ".$query . "<br><br>";

	$result = mysqli_query($conn, $query);

	if (!$result)
        throw new Exception($conn->error);
	
	$conn->commit();
	$conn->autocommit(TRUE);
    $conn->close();
   } //try
  catch ( Exception $e )
   {
	echo $conn->error;
    $conn->rollback(); 
    $conn->autocommit(TRUE); // i.e., end transaction
	$conn->close();
    $mymsg = "nuova_assoc_casa() Errore modifica casa" . $conn->error;
    echo $mymsg;
	return-1;
   }
   $mymsg = "Modifica casa effettuata correttamente";
   echo $mymsg;
   
 return 0;
}





/*
*** cancella_casa: effettua la DELETE su DB di una casa
*** input: id casa
*** return: -1 errore
***         0 OK
*/
function cancella_casa($id)
{
 include ("db_conn.php");

// lettura dal file geojson
$points = file_get_contents('points.geojson');
$pointsarray = json_decode($points, true);

 $i = 0;
 foreach ($pointsarray["features"] as $key => $item)
  {
      if ($item["properties"]["name"] == $id)
		  {
            break;
          }
       $i++;
  }
	 

	$query = "DELETE  FROM casa ";
    $query .= "WHERE id = " . $id ;
	
//	echo "<br> query: ". $query . "<br><br>";

	$result = mysqli_query($conn, $query);

	if (!$result)
	  {
	    echo 'Errore istruzione SQL\n';
		echo  $query;
		return -1;
	  }
	$i++;
   
   echo "<br> numero righe cancellate su DB=".  mysqli_affected_rows($conn). "<br>";

// close connection 
mysqli_close($conn);
echo "<br>  casa cancellata correttamente su DB\n";

 return 0;
}


/*
*** modifica_casa: effettua la UPDATE su DB dei dati di una casa
*** richiamata da edit.php
*** input: id casa
*** return: -1 errore
***         0 OK
*/
function modifica_casa($id)
{
 include ("db_conn.php");

// lettura dal file geojson
$points = file_get_contents('points.geojson');
$pointsarray = json_decode($points, true);

 $i = 0;
 foreach ($pointsarray["features"] as $key => $item)
  {
      if ($item["properties"]["name"] == $id)
		  {
            break;
          }
       $i++;
  }

	 $idc = $pointsarray["features"][$i]["properties"]["name"];
//	 print '<b>id casa:'.$idc.':</b><br>';
	 
	 $tag = $pointsarray["features"][$i]["properties"]["tag"];
//	 print '<b> tag:'.$tag.':</b><br>';

	 $data_val = $pointsarray["features"][$i]["properties"]["verified"];
//	 print '<b> data validazione:'.$data_val.':</b><br>';
	
	 $id_osm_new = $pointsarray["features"][$i]["properties"]["description"]["id OSM"];
//	 print '<b>id OSM:'.$id_osm.':</b><br>';

	 $nome_casa_new = $pointsarray["features"][$i]["properties"]["description"]["Nome Casa"];
//	 print '<b>nome casa:'.$nome.':</b><br>';

	 $moranca = $pointsarray["features"][$i]["properties"]["description"]["Moranca"];
//	 print '<b> moranca:'.$moranca.':</b><br>';

     $capof = $pointsarray["features"][$i]["properties"]["description"]["Capo Famiglia"];
//	 print '<b> capo famiglia:'.$capof.':</b><br>';

	 $numper = $pointsarray["features"][$i]["properties"]["description"]["Numero Persone"];
//	 print '<b> numero persone:'.$numper.':</b><br>';

	 $lon = $pointsarray["features"][$i]["geometry"]["coordinates"][0];
//	 print '<b> longitudine:'.$lon.':</b><br>';

	 $lat = $pointsarray["features"][$i]["geometry"]["coordinates"][1];
//	 print '<b> latitudine:'.$lat.':</b><br>';
try 
 {

  /*
  *** recupera i dati della casa
  */
   $query =  "SELECT c.nome as nome_casa,";
   $query .= "c.id_osm as id_osm, c.data_inizio_val ";
   $query .= " FROM casa c ";
   $query .= " WHERE c.id =". $idc;
   $query .= " AND c.data_fine_val is null";
//   echo "q2: ". $query. "<br>";

   $result = $conn->query($query);
   if (!$result)
      throw new Exception($conn->error);

   $row = $result->fetch_array(); 
  
   $upd = false;
   $id_osm = $row['id_osm'];
   if($id_osm == '')
     $id_osm = 0;
  
  if($id_osm != $id_osm_new)
	   $upd = true;

   if (($nome_casa_new !=  $row['nome_casa']) ||
       ($id_osm_new != $id_osm))
     $upd = true;
   else
     $upd = false;
    if (!$upd)
     {
      $mymsg = "Non sono state effettuate modifiche";
      echo $mymsg;
	  return 0;
     }
    
	$conn->query("START TRANSACTION"); //inizio transazione
   
     $data_attuale = date('Y/m/d');

// inserimento nello storico case

      $tipo_operazione = "(MOD da mappa)";
       
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
      $query .= $idc.",";  
      $query .= "'".$row['nome_casa']."',";
      $query .= "NULL,";	// id_moranca
      $query .= "NULL,";	// nome_moranca
      $query .= $row['id_osm'].",";
	  $query .= "NULL,";	// capo famiglia
      $query .= "'".$row['data_inizio_val']."',";  //data inizio val
	  $query .= "'".$data_attuale."')";		//data fine val

      $result = $conn->query($query);
 //    echo "q3:". $query . "<br>";
       
	  if (!$result)
        throw new Exception($conn->error);
     

	$query = "UPDATE casa SET ";
	$query .= "nome = '" . $nome_casa_new . "',";
	$query .= "id_osm = " . $id_osm_new . ",";
    $query .= "lat = " . $lat . ",";
    $query .= "lon = " . $lon . ",";
    $query .= "data_inizio_val =  STR_TO_DATE('". $data_val ."', '%d/%m/%Y')";
	$query .= " WHERE id = " . $idc; 

//	echo "<br> query: ".$query . "<br><br>";

	$result = mysqli_query($conn, $query);

	if (!$result)
        throw new Exception($conn->error);
	
	$conn->commit();
	$conn->autocommit(TRUE);
    $conn->close();
   } //try
  catch ( Exception $e )
   {
	echo $conn->error;
    $conn->rollback(); 
    $conn->autocommit(TRUE); // i.e., end transaction
	$conn->close();
    $mymsg = "modifica_casa(): Errore modifica  casa in db_util.php" . $conn->error;
    echo $mymsg;
	return -1;
   }
   $mymsg = "Modifica casa effettuata correttamente";
   echo $mymsg;
   
 return 0;
}



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
*** ret_code: 0 = OK
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