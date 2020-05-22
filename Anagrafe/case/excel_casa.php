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
isLogged("gestore");
?>
 <?php
 $util2 = $config_path .'/../db/db_conn.php';
 require_once $util2;
?>
<?php
error_reporting(0);
$zona=$_POST["zona"] ;
$filename=$_POST["file"];
$oraoggi=date("Y/m/d");
$query = "SELECT c.id, c.nome,";
$query .= " z.nome zona, c.id_moranca, m.nome nome_moranca,";
$query .= " c.nome, p.id id_pers, p.nominativo, c.id_osm as id_osm, ";
$query .= " c.data_inizio_val data_val, c.data_fine_val";
$query .= " FROM morance m INNER JOIN casa c ON m.id = c.id_moranca ";
$query .= " INNER JOIN zone z  ON  z.cod = m.cod_zona ";
$query .= " LEFT JOIN pers_casa pc ON c.id  = pc.id_casa ";
$query .="  AND pc.cod_ruolo_pers_fam = 'CF'";
$query .="  LEFT JOIN persone p ON p.id = pc.id_pers";
$query .= " WHERE c.DATA_FINE_VAL is null AND";
$query .= " m.cod_zona like '$zona';";

$result = $conn->query($query);
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