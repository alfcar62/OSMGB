<?php
/*
*** insert_casa.php
*** effettua insert su tabella casa
*** Richiamato da ins_casa.php
*** 15/3/2020: A.Carlone
*** 25/02/2020  Ferraiuolo: Modifica:rimosso inserimento capofamiglia
*/
$config_path = __DIR__;
//$util1 = "E:/xampp/htdocs/OSM/Anagrafe/util.php";
$util1="../util.php";
//$util2 = "E:/xampp/htdocs/OSM/Anagrafe/db/db_conn.php";
 $util2="../db/db_conn.php";
require_once $util2;
require_once $util1;
setup();
isLogged("gestore");
$id_moranca=$_POST["add_moranca"];

$id_osm=$_POST["id_osm"];
$id_osm=stripslashes($id_osm);						//protezione da sql injection
$id_osm=mysqli_real_escape_string($conn,$id_osm);	//protezione da sql injection
if ($id_osm == "")
  $id_osm = 0;

$nome=$_POST["nome"];
$nome=stripslashes($nome);						//protezione da sql injection
$nome=mysqli_real_escape_string($conn,$nome);	//protezione da sql injection

$dataInizio=date("Y/m/d"); 
$query="select max(id) as massimo from casa";
$result=$conn->query($query);
$row=$result->fetch_array();
$massimo=$row["massimo"]+1;
$query2="INSERT INTO casa (id, nome, id_moranca,id_osm,data_inizio_val) VALUES (".$massimo.",'".$nome."',".$id_moranca.",".$id_osm.",'".$dataInizio."')";
//echo $query2;
$result = $conn->query($query2);
if ($result)
 {
   $conn->close();
   $mymsg = "Inserimento casa effettuato correttamente id = ". $massimo;
   EchoMessage($mymsg, "gest_case.php");
 }
else
 {
   $conn->close();
   $mymsg = "Errore inserimento casa" . $conn->error;
   EchoMessage($mymsg, "gest_case.php");
 }
?>