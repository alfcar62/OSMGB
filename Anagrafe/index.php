<!DOCTYPE html>
<html>
<?php
$config_path = __DIR__;
$util = $config_path .'/util.php';
require $util;

setup();
?>
<?php stampaIntestazione(); ?>
<body>
<?php stampaNavbar(); 
$lang=isset($_SESSION['lang'])?$_SESSION['lang']:"ITA";
$jsonFile=file_get_contents(__DIR__ ."/gestione_lingue/translations.json");//Converto il file json in una stringa
$jsonObj=json_decode($jsonFile);//effettuo il decode della stringa json e la salvo in un oggetto
//echo json_last_error_msg();
 ?>
<div align='center'>
    <br>
   <?php 
    if(isset($_GET['welcome']))
    echo "<h2>Benvenuto {$_SESSION['nome']},hai i permessi di {$_SESSION['tipo']}</h2>";
    ?>

    <br>
    
 <IMG SRC="img/bandiera.jpg" WIDTH="146" HEIGHT="74" BORDER="0" ALT="">
</div>
 <?php 
 echo "<div align='center'>";
 //echo "<h1>Anagrafe Web e Mappa OpenStreetMap ";
 //echo "<h1>di moran&ccedil;e, case e strade <br> del villaggio di Ntchangue (Guinea Bissau)</h1>";
    echo "<h1>".$jsonObj->{$lang."Index"}[0]."</h1>"; 
 echo "<br><br><br>";
 //echo "<h2>Un progetto realizzato per  AbalaLite</h1>";
 //echo "<h2>dalla classe 5A sez. Informatica a.s. 2019/20 dell'IIS A. Avogadro di Torino</h1>"; 
    echo "<h2>".$jsonObj->{$lang."Index"}[1]."</h2>";
 echo "</div>";
 echo "<div align='center'>";
 echo " <IMG SRC='img/logo_OSMGB.jpeg' WIDTH='227' HEIGHT='192' BORDER='0' ALT=''>";
 //echo "<IMG SRC='img/abalalite.jpg' WIDTH='227' HEIGHT='192' BORDER='0' ALT='abalalite'>";
// echo "<IMG SRC='img/logo_avogadro.jpg' WIDTH='227' BORDER='0' ALT='avogadro'>";
 echo "</div>";
 ?>

</body>
</html>