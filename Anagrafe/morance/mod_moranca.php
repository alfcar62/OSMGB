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
setup();
isLogged("utente");
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
$zona = $row['zona'];

$id_osm = $row['id_osm'];

echo "<h3>Modifica moran&ccedil;a: $moranca (id =$row[id]), zona: $zona<h3>";//Inserimento moranca

echo "Nome moran&ccedil;a: <input type='text' name='nome_moranca' value='$moranca' required><br>";//Nuovo nome morança

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

?>
sulla mappa: <input type='text' name='id_osm'><span id="info"><img onmouseover="tooltip(event)" onmouseout="tooltip(event)" src="../img/infoIcon.png" style="height:25px;width:50px;"></span>
 <span id="error" style="visibility:hidden">Identificativo della moran&ccedil;a sulla mappa OpenStreetMap:<br> 1. vai sulla mappa OSM,<br> 2. cerca la moran&ccedil;a,<br> 3. clicca con il pulsante destro del mouse, scegli 'ricerca di elementi' <br>4.  copia qui il numero dell'oggetto relativo (il numero senza #)</span><br>

<?php
echo "<button type='submit' class = 'button'>".$jsonObj->{$lang."Morance"}[4]."</button>";//Conferma
echo "</form>";
    echo "<h2>MODIFICA LA FOTO DELLA MORANCA :</h2>";

if(isset($_POST["caricaFoto"])) {
	$target_dir = "immagini/";
	$target_file = $target_dir .$id_moranca.'.'.pathinfo($_FILES["fileToUpload"]["name"] ,PATHINFO_EXTENSION);
	$flagUpload = true; //flag che mi servirà alla fine per capire se è possibile caricare l'immagine
	$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
	// Controllo se il file caricato è un immagine

	$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);//se resituisce true è un immagine
	if($check == true) {
		// Check file size
		if ($_FILES["fileToUpload"]["size"] > 500000) {
			echo "Errore: l'immagine è troppo grande";
			$flagUpload = false;
		}
		// Consento soltanto alcuni formati
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
			echo "Errore: è consentito caricare soltanto JPG,PNG o JPEG";
			$flagUpload = false;
		}
		//Controllo il flag
		if ($flagUpload == true) {
			// Elimino eventuali file presenti con lo stesso nome (in caso stessi sostituendo l'immagine della casa)
			$files = glob($target_dir .$id_moranca.'*');//array
			foreach ($files as $file) {
				unlink($file);
			}
			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
				echo "L'immagine è stata caricata ";
			}
		} 

	}else{
		echo "Errore:si prega di caricare un immagine";    

	}}




if(isset($_POST["eliminaFoto"])) {
	// Elimino la foto
	$target_dir = "immagini/";
	$files = glob($target_dir.$id_moranca.'*');//array
	foreach ($files as $file) {
		unlink($file);
	}


}


echo '  <form action="mod_moranca.php" method="post" enctype="multipart/form-data">';//form per caricare la foto
echo "Seleziona una foto da caricare:";
echo   " <input type='hidden' name='id_moranca' value='$id_moranca' >";//parametro che mi serve mantenere dopo aver ricaricato la pagina
echo '<input type="file" name="fileToUpload" id="fileToUpload" required>
<input type="submit"  value="Carica foto" name="caricaFoto">
</form>   ';
$immagine=glob('immagini/'.$id_moranca.'.*');//uso la funzione glob al posto di if_exist perchè permette di mettere * al posto dell'estensione.Se restituisce qualcosa ha trovato l'immagine
if($immagine != null){

	echo "Foto attuale:";
	echo "<img src='$immagine[0]'  width='120'
height='120' id='image' style=' display: block;
margin-left:0;'  > ";
	echo '  <form action="mod_moranca.php" method="post" enctype="multipart/form-data">';//form per caricare la foto
	echo   " <input type='hidden' name='id_moranca' value='$id_moranca' >";//parametro che mi serve mantenere dopo aver ricaricato la pagina
	echo '<input type="submit" value="Elimina foto" name="eliminaFoto"></form>   ';
}
else{
	echo 'Attualmente non è presente alcuna foto';
}
echo "<br><a href='gest_morance.php?pag=$pag'>Torna a gestione morance</a>" 
?>
</body>
</html>