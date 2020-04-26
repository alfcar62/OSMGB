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
     $id_osm = 0;
   
   if (($nome_casa_new !=  $row['nome_casa']) ||
      ($id_moranca_new != $row['id_moranca']) ||
      ($id_osm_new != $id_osm))
     $upd = true;
   else
     $upd = false;
  

   if ($upd)
   { 
	 $lat = 0.0;
     $lon = 0.0;
	 if ($id_osm_new !=0)
	  {	  
	   $result = get_latlon($id_osm_new, $lat, $lon);   // recupero latitudine e longitudine a partire dall'id_osm
       if ($result<0)
        echo "errore accesso a OpenStreetMap";
      }
	
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
   if (($lat !=0.0) && ($lon !=0.0))
     {
	   $query .= "lat       = ". $lat.",";
	   $query .= "lon       = ". $lon.",";
     }
   $query .= "data_inizio_val='".$data_attuale."'";
   $query .= " WHERE casa.id = ".$id_casa;
//   echo "q4 ".$query;
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

<?php
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
	   echo "<br>modifica_casa.php: errore curl_init():inizializzazione con OSM non stabilita";
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
	 echo "<br>modifica_casa.php: errore curl_exec()esecuzione curl OSM errore";
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
?>