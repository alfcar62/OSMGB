<?php
/*
*** Input:
*** gest_persone.php
*** Output. excel.php
Questo file serve a scegliere la query da eseguire attraverso menù a tendina
*** 25/03/2020 M. Scursatone : Creazione file e prima implementazione
*** 03/04/2020 M.Scursatone : Modifiche 
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
require_once $util1;
setup();
isLogged("gestore");
$lang=isset($_SESSION['lang'])?$_SESSION['lang']:"ITA";
$jsonFile=file_get_contents("../gestione_lingue/translations.json");//Converto il file json in una stringa
$jsonObj=json_decode($jsonFile);//effettuo il decode della stringa json e la salvo in un oggetto
?>


 <?php
 $util2 = $config_path .'/../db/db_conn.php';
 require_once $util2;
?>
<?php stampaIntestazione(); ?>
<body>
<?php stampaNavbar(); 
echo "<h2> Villaggio di NTchangue:";
echo "Export su file Excel delle persone </h2>";
?>

<h2>
Selezione del tipo di dato da esportare su file excel
</h2>
<br>

<div  position="absolute"  align="center">
<form action="excel_persona.php" method="post" >
Zona:
<select name="zona">
<option value="%">tutte</option>   
<option value="N">nord</option>
<option value="O">ovest</option>
<option value="S">sud</option>
</select>
Sesso:
<select name="sesso">
<option value="%">tutti</option>
<option value="m">maschi</option>
<option value="f">femmine</option>
</select>
<br>
Età: 
<select name="eta">
<option value="%">tutte</option>
<option value="minorenni">minorenni</option>     
<option value="maggiorenni">maggiorenni</option>
</select>
Ordinato in base a:
<select name="order">
<option value="nominativo">nome</option>
<option value="data_nascita">età</option>
<option value="id">ID</option>
</select>
<br>
Nome del file da scaricare:
<input type="text" name="file" placeholder="*Scrivi un nome*"  value="">
.xls
<br>
<input type='submit' class='button' name='invia'>
</form>  