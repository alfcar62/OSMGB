<?php
//Data ultima modifica:12/03/2020    Autore:Gobbi Dennis
//Descrizione:Adattamento al nuovo metodo di selezione per la lingua
session_start();
//$lang=$_POST['lang'];per il vecchio select,tengo in caso di backup
$langs=$_GET['lang'];
echo "Prova:$langs<br>";
//$dir=$_GET['dir'];//mi salvo la dir per il reindirizzamento,work in progress
//print_r( $_REQUEST);
//echo $dir;
//echo $lang;
$_SESSION['lang']=$langs;
header("Location:../index.php");

?>