<?php
/*
*** aggiungi_moranca.php
*** inserimento nuova moranca
*** 13/3/2010: A. Carlone:      effettuate alcune correzioni 
*** 28/03/20    Gobbi Dennis:  Implementazione della gestione multilingue
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
setup();
isLogged("gestore");
?>
<?php stampaIntestazione(); ?>
<body>
<?php stampaNavbar(); ?>
<?php
$nome_moranca=stripslashes($_POST["nome_moranca"]);//protezione da sql injection
$nome_moranca=mysqli_real_escape_string($conn,$nome_moranca);//protezione da sql injection

$cod_zona=stripslashes($_POST["cod_zona"]);//protezione da sql injection
$cod_zona=mysqli_real_escape_string($conn,$cod_zona);//protezione da sql injection


$id_osm = stripslashes($_POST["id_osm"]);//protezione da sql injection
$id_osm=mysqli_real_escape_string($conn,$id_osm);//protezione da sql injection
if ($id_osm == "")
  $id_osm = 0;

$result = $conn->query("SELECT count(ID) as cont from morance where nome = '$nome_moranca'");
$row = $result->fetch_array();
$cont=$row['cont'];
if ($cont>0)
   EchoMessage("Moranca inesistente: impossibile inserire", "gest_morance.php");
else
{

$dataOggi=date("Y/m/d"); 
$result = $conn->query("SELECT max(ID) as massimo from morance");
$row = $result->fetch_array();
$massimo=$row['massimo']+1;

$id_mor_zona = 0;
//echo $nome_zona;
$query =  "INSERT INTO morance ";
$query .= "(ID, NOME, ID_MOR_ZONA, COD_ZONA,ID_OSM, DATA_INIZIO_VAL) ";
$query .= " values ";
$query .= "($massimo,'$nome_moranca',$id_mor_zona, '$cod_zona',$id_osm,'$dataOggi')";
//echo $query;

$result = $conn->query($query);
if ($result)
  {
   $conn->close();
   $mymsg = "Inserimento moranca effettuato correttamente id= ". $massimo;
   EchoMessage($mymsg, "gest_morance.php");
   }
  else
   {
    $mymsg = "Errore inserimento moranca: ";
	$mymsg .= $conn-error;
	
	$conn->close();
	EchoMessage($mymsg, "gest_morance.php");

   }
 }
?>
</body>