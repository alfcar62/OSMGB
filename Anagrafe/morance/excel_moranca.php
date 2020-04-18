<?php
/*
*** Input:
*** export.php
*** Output. file .xls
***
Questo file serve  a Scaricare in locale con estensione.xls una tabella ricevuta dal db dopo opportuna query a scelta dell'utente
*** 03/04/2020 M.Scursatone : Creazione file e prima implementazione
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
require_once $util1;
setup();
isLogged("utente");
$lang=isset($_SESSION['lang'])?$_SESSION['lang']:"ITA";
$jsonFile=file_get_contents("../gestione_lingue/translations.json");//Converto il file json in una stringa
$jsonObj=json_decode($jsonFile);//effettuo il decode della stringa json e la salvo in un oggetto
?>


 <?php
 $util2 = $config_path .'/../db/db_conn.php';
 require_once $util2;
?>
<?php
$zona=$_POST["zona"] ;
$filename=$_POST["file"];
$oraoggi=date("Y/m/d");
$query = "SELECT ";
$query .= " m.id, m.nome, m.cod_zona ,m.id_mor_zona,";
$query .= " m.data_inizio_val, m.data_fine_val";
$query .= " FROM morance m, zone z ";
$query .= " WHERE m.cod_zona like '$zona';";

$result = $conn->query($query);
$righe = $result->fetch_array(MYSQLI_ASSOC);
if($zona=="%"){
    $zona="tutte";
}
$output=" Questa tabella e stata generata dall'applicazione web<br>";
$output .= "Questa tabella contiene le morance della zona '$zona': creata il '$oraoggi'<br>";
$output .= ("<table id=\"table\" border=\"1\"><tr id=\"riga\">");
foreach ($righe as $chiave => $valore) {
$output .=( "<th align=\"center\">" . $chiave . "</th>");
        }
$output .=("</tr>");

while ($righe = $result->fetch_array(MYSQLI_ASSOC)) {
    $prima = true;
    $output .=("<tr id=\"rigaQuery\">");
    foreach ($righe as $chiave => $valore) {

        $output .=("<td align=\"center\">" . $valore . "</td>");
        }
     $output .=("</tr>");
    }
$output .= ("</table>");
$output .=("<br>");
header('Content-Type: application/xls');
header('Content-Disposition: attachment; filename='.$filename.'.xls');
echo $output;
error_reporting(0);
?>