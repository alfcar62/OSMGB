<?php

/*
*** db_osm_util.php : funzioni di utilità per integrazione tra OSM e database
*/

/*
*** inserisci_casa: effettua la INSERT su DB di una casa
*** input: id casa
*** return: -1 errore
***         0 OK
*/
function inserisci_casa($id)
{
 include ("../db/db_conn.php");

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
 include ("../db/db_conn.php");

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

   $result = mysqli_query($conn, $query);
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
*** delete_assoc_casa(): effettua la cancellazione della associazione tra casa e id_osm
*** input: id casa
*** return: -1 errore
***         0 OK
*/
function delete_assoc_casa($id)
{
 include ("../db/db_conn.php");

 $query = "UPDATE casa SET id_osm = NULL, lat= NULL, lon= NULL ";
 $query .= "WHERE id = " . $id ;
	
//	echo "<br> query: ". $query . "<br><br>";

 $result = mysqli_query($conn, $query);

 if (!$result)
  {
   echo 'Errore istruzione SQL\n';
   echo  $query;
   return -1;
  }

// close connection 
mysqli_close($conn);
echo "<br> associazione casa cancellata correttamente su DB\n";

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
 include ("../db/db_conn.php");

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
 include ("../db/db_conn.php");

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

   $result = mysqli_query($conn, $query);
   if (!$result)
      throw new Exception($conn->error);

   $row = $result->fetch_array(); 
  
   $upd = false;
   $id_osm = $row['id_osm'];
   if($id_osm == '')
     $id_osm = 0;
  
  if ($id_osm != $id_osm_new)
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
	if (($id_osm_new == '') ||($id_osm_new == 0))
      {
       $query .= "lat =  NULL,";
       $query .= "lon =  NULL,";
      }
	else
      {
       $query .= "lat = " . $lat . ",";
       $query .= "lon = " . $lon . ",";
      }
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
*** funzione che, a partire dall'id su OSM ritorna latitudine e longitudine
*** return 0  = ok
***        -1 = fail
*** utilizzo della libreria Curl (per le connessioni server to server
*** NB: deve essere attivato come parametro di configurazione PHP
*** ad es: Su altervista si deve andare nel pannello di controllo e abilitare
*** le connessioni server to server
*/

function get_latlon($id_osm, &$lat, &$lon)
{ 
// a little script check is the cURL extension loaded or not

 $url = "https://api.openstreetmap.org/api/0.6/way/".$id_osm."/full.json";
// echo "url=". $url;

 $client = curl_init($url);		// inizializzazione
 if ($client == false)
	 { 
//	   echo "<br>modifica_casa.php: errore curl_init():inizializzazione con OSM non stabilita";
	   return -1;
     }
	
 curl_setopt($client,CURLOPT_RETURNTRANSFER,true);	// output come stringa

 curl_setopt($client, CURLOPT_HEADER, 0);		// non scarico header

 curl_setopt($client, CURLOPT_TIMEOUT, 20);		// set timeout

 curl_setopt($client, CURLOPT_SSL_VERIFYHOST, FALSE);

 curl_setopt($client, CURLOPT_SSL_VERIFYPEER, FALSE);

 $response = curl_exec($client);

 if ($response == false)
   { 
//	 echo "<br>modifica_casa.php: errore curl_exec()esecuzione curl OSM errore";
	 return -1;
   }
 else
  {
   // echo $response;

    $arr = json_decode($response,true);
 //   var_dump($arr);
    $lat = $arr['elements'][0]['lat'];
	$lon = $arr['elements'][0]['lon'];

 //   echo "lat=".  $lat;
 //   echo "lon=".  $lon;

    curl_close($client);
  }
  return 0;
}



/*
*** funzione che, a partire da lat e lon ritorna l'id del nodo su OSM
*** return 0  = ok
***        -1 = fail
*** utilizzo della libreria Curl (per le connessioni server to server
*** NB: deve essere attivato come parametro di configurazione PHP
*** ad es: Su altervista si deve andare nel pannello di controllo e abilitare
*** le connessioni server to server
*/

function get_osm_id($lat, $lon, &$id_osm)
{ 
/* uso di nominatim reverse: ma non ritorna il valore corretto
// $email = "acarlone@itisavogadro.it";		// per le policy di OSM bisogna identificarsi
// chiamata di API di OSM: reverse nominatim
// https://nominatim.org/release-docs/develop/api/Reverse/
/*
 $url= "https://nominatim.openstreetmap.org/reverse?format=json";
 $url .= "&lat=".$lat."&lon=".$lon. "&osm_type=W";
 $url .= "&email=".$email;
*/

/*
 $url="http://overpass-api.de/api/interpreter?data=[out:json];";
 $url .= "way['building'='house'](around:5,";
 $url .= $lat .",". $lon . ");out;";
*/
$url="http://overpass-api.de/api/interpreter?data=[out:json];";
$url .= "way['building'](around:5,";
$url .= $lat .",". $lon . ");out;";
// echo "url=". $url;

 $client = curl_init($url);		// inizializzazione
 if ($client == false)
	 { 
//	   echo "<br>get_osm_id(): errore curl_init():inizializzazione con OSM non stabilita";
	   return -1;
     }
	
 curl_setopt($client,CURLOPT_RETURNTRANSFER,true);	// output come stringa

 curl_setopt($client, CURLOPT_HEADER, 0);		// non scarico header

 curl_setopt($client, CURLOPT_TIMEOUT, 20);		// set timeout

 curl_setopt($client, CURLOPT_SSL_VERIFYHOST, FALSE);

 curl_setopt($client, CURLOPT_SSL_VERIFYPEER, FALSE);

 $response = curl_exec($client);

 if ($response == false)
   { 
//	 echo "<br>get_osm_id(): errore curl_exec()esecuzione curl OSM errore";
	 return -1;
   }
 else
  {
//  echo $response;
    $arr = json_decode($response,true);
//    var_dump($arr);
 //   $id_osm = $arr['osm_id'];		// vale  per nominatim
	 if (isset($arr['elements'][0]['id']))
		 $id_osm = $arr['elements'][0]['id'];
	 else
     { 
      //	 echo "<br>get_osm_id(): punto non trovato";
	  return -1;
     }
 // echo "id_osm".  $id_osm;
    curl_close($client);
  }
  return 0;
}
?>