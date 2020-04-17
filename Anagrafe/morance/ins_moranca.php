<?php
/*
*** ins_moranca.php
*** prepara l'inserimento nuova moranca
*** attiva insert_moranca.php
*** 13/3/2010: A. Carlone:      effettuate alcune correzioni 
*** 28/03/20    Gobbi Dennis:  Implementazione della gestione multilingue
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
isLogged("utente");
setup();
?>
<?php stampaIntestazione(); ?>
<body>
<?php stampaNavbar(); ?>
<?php
$lang=isset($_SESSION['lang'])?$_SESSION['lang']:"ITA";
$jsonFile=file_get_contents("../gestione_lingue/translations.json");//Converto il file json in una stringa
$jsonObj=json_decode($jsonFile);//effettuo il decode della stringa json e la salvo in un oggetto
//echo $jsonObj->{$lang."Morance"}[0]
echo "<br>".$jsonObj->{$lang."Morance"}[16]."<br><br>";//Inserimento moranca

echo "<form action='insert_moranca.php' method='post'>";
echo  $jsonObj->{$lang."Morance"}[5].": <input type='text' name='nome_moranca' required placeholder='".$jsonObj->{$lang."Morance"}[17]."' ><br>";//Nome

echo "id OSM: <input type='text' name='id_osm' ><br>";

echo   $jsonObj->{$lang."Morance"}[6].": <select name='cod_zona'>";//Zona
echo "<option value=' '></option><br>";
$result = $conn->query("SELECT * FROM zone");

$nr=$result->num_rows;
for($i=0;$i<$nr;$i++)
{
  $row = $result->fetch_array();
  if($row["NOME"]!=null || $row["NOME"]!="")
   {
     echo "<option value='".$row["COD"]."'>".$row["NOME"]."</option>";
   }
}
echo "</select></br>";

echo "<button type='submit' >".$jsonObj->{$lang."Morance"}[4]."</button>";//Invia/Conferma
echo "</form>";
?>
</body>
</html>