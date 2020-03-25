<?php
/*
Questo file serve 
25/03/2020 M. Scursatone : Creazione file e prima implementazione
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
require_once $util1;
setup();
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
<?php stampaNavbar(); ?>


<h2>
SCEGLI IL TIPO DI EXPORT SU FILE EXCEL CHE DESIDERI
</h2>
<br>

<div  position="absolute"  align="center">
<form action="excel.php" method="post" >
SELEZIONARE LA ZONA:
<select name="zona">
<option value="%">tutto</option>   
<option value="N">nord</option>
<option value="O">ovest</option>
<option value="S">sud</option>
</select>
SELEZIONARE IL SESSO:
<select name="sesso">
<option value="%">maschi e femmine</option>
<option value="m">maschi</option>
<option value="f">femmine</option>
</select>
<br>
SELEZIONARE LE ETA VOLUTE: 
<select name="eta">
<option value="minorenni">minorenni</option>     
<option value="maggiorenni">maggiorenni</option>
</select>
SELEZIONARE L'ORDINE:
<select name="order">
<option value="nominativo">nome</option>
<option value="data_nascita">età</option>
<option value="id">ID</option>
</select>
<br>
NOME DEL FILE DA SCARICARE:
<input type="text" name="file" placeholder="*Scrivi un nome*"  value="">
<br>
<input type='submit' name='invia'>
</form>  