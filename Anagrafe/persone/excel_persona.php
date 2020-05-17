<?php
/*
*** Input:
*** export.php
*** Output. file .xls
***
Questo file serve  a Scaricare in locale con estensione.xls una tabella ricevuta dal db dopo opportuna query a scelta dell'utente
*** 25/03/2020 M.Scursatone : Creazione file e prima implementazione
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
<?php 
$zona=$_POST["zona"] ;
$sesso=$_POST["sesso"] ;
$eta=$_POST["eta"] ;
$filename=$_POST["file"];
$oraoggi=date("Y/m/d");
$ordine=$_POST["order"];
if($eta=="%"){
    $sceltaeta=" ";
    $eta="tutte";
}else{
if($eta=="minorenni"){
    $sceltaeta= "and DATEDIFF('$oraoggi',p.data_nascita)<6570 ";
}else {
    $sceltaeta= "and DATEDIFF('$oraoggi',p.data_nascita)>6570 ";
}}
$query = "SELECT ";
$query .= " p.id, p.nominativo, p.sesso, p.data_nascita, p.data_morte,";
$query .= " c.id as id_casa, c.id_moranca,c.nome nome_casa, m.nome nome_moranca,";
$query .= " m.cod_zona,  c.id_casa_moranca, ";
$query .= " pc.cod_ruolo_pers_fam, rpf.descrizione,s.matricola, s.data_inizio_val, s.data_fine_val";
$query .= " FROM persone p";
$query .= " INNER JOIN pers_casa pc ON  pc.id_pers = p.id";
$query .= " INNER JOIN casa c ON  pc.id_casa = c.id";
$query .= " INNER JOIN morance m ON  c.id_moranca = m.id";
$query .= " INNER JOIN ruolo_pers_fam rpf ON  pc.cod_ruolo_pers_fam = rpf.cod";
$query .= " LEFT JOIN studenti s ON  s.matricola = p.matricola_stud";
$query .= " where p.sesso like '$sesso' and m.cod_zona like '$zona' ".$sceltaeta." order by '$ordine'; ";
$result = $conn->query($query);
$nr = $result->num_rows;
if($zona=="%"){
    $zona="qualsiasi";
}
if($sesso=="%"){
    $sesso="qualsiasi";
}
$output="Questa tabella e stata generata dall'applicazione web<br>";
$output .= "Questa tabella contiene le persone della zona '$zona', di sesso '$sesso', con eta '$eta', in ordine di '$ordine': creata il '$oraoggi'<br>";
$righe = $result->fetch_array(MYSQLI_ASSOC);
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