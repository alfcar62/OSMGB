<head>
   <title>MODIFICA MORANCA</title>
   <?php
//Data ultima modifica:29/02/20    Autore:Gobbi Dennis
//Descrizione:Implementazione della gestione multilingue
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
isLogged("utente");
setup();
$pag=$_SESSION['pag_m']['pag_m'];
//unset($_SESSION['pag_m']);
$lang=isset($_SESSION['lang'])?$_SESSION['lang']:"ITA";
$jsonFile=file_get_contents("../gestione_lingue/translations.json");//Converto il file json in una stringa
$jsonObj=json_decode($jsonFile);//effettuo il decode della stringa json e la salvo in un oggetto

?>
<html>
<?php stampaIntestazione(); ?>
<body>
<?php stampaNavbar();

$id_moranca=$_POST["id_moranca"];

echo "<br>".$jsonObj->{$lang."Morance"}[19]." ";//Modifica Morança

//$conn->query("START TRANSACTION"); //inizio transazione


echo "<form action='modifica_moranca.php' method='post'>";

$query =  "SELECT m.ID id, m.NOME 'nome_moranca', m.cod_zona, z.nome zona, m.id_osm  ";
$query .= "FROM morance m INNER JOIN zone z ON m.cod_zona = z.cod ";
$query .= "WHERE  id='$id_moranca'";
//$query .= " FOR UPDATE";

//echo $query;
$result = $conn->query("$query");

$row = $result->fetch_array();

//$conn->query("LOCK TABLE morance WRITE"); // WRITE/READ

$moranca = utf8_encode ($row['nome_moranca']) ;
$cod_zona = $row['cod_zona'];
$id_osm = $row['id_osm'];

echo "<br>".$jsonObj->{$lang."Morance"}[20].":". $row['id'];//Id morança
echo "<br>".$jsonObj->{$lang."Morance"}[5].":". $moranca;//Nome morança
echo "<br>".$jsonObj->{$lang."Morance"}[6].":". $cod_zona;//zona
echo "<br> id OSM:". $id_osm;

echo  "<br><br><br>";
echo $jsonObj->{$lang."Morance"}[18] .": <input type='text' name='nome_moranca' value='$moranca' ><br>";//Nuovo nome morança
echo "id OSM: <input type='text' name='id_osm' value='$id_osm' ><br>";

echo "<input type='hidden'  name='id_moranca'  value=$id_moranca>";

//Select option per la scelta della zona
echo   $jsonObj->{$lang."Morance"}[3].": <select name='cod_zona'>";
$result = $conn->query("SELECT * FROM zone");
$nz=$result->num_rows;
for($i=0;$i<$nz;$i++)
{
 $row = $result->fetch_array();

 if($cod_zona == $row["COD"])
			echo "<option value='".$row["COD"]."' selected>". $row["NOME"]." </option>";
		else
			echo "<option value='".$row["COD"]."'>".$row["NOME"]."</option>";
}
echo "</select>";


echo "<button type='submit' class = 'button'>".$jsonObj->{$lang."Morance"}[4]."</button>";//Conferma
echo "</form>";
echo "<br><a href='gest_morance.php?pag=$pag'>Torna a gestione morance</a>" 
?>
</body>
</html>